<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Core;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\ModuleInterface as ModuleInterface;
use PublishPress\Future\Framework\WordPress\Facade\NoticeInterface;
use PublishPress\Future\Modules\Expirator\Controllers\PluginsListController;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;
use PublishPress\Future\Modules\Expirator\Migrations\V30000ActionArgsSchema;
use PublishPress\Future\Modules\Expirator\Migrations\V30000ReplaceFooterPlaceholders;
use PublishPress\Future\Modules\Expirator\Migrations\V30000WPCronToActionsScheduler;
use PublishPress\Future\Modules\Expirator\Migrations\V30001RestorePostMeta;
use PublishPress\Future\Modules\Expirator\Migrations\V30104ArgsColumnLength;
use PublishPress\Future\Modules\Expirator\PostMetaAbstract;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use PublishPress\Future\Modules\Workflows\Migrations\V40000WorkflowScheduledStepsSchema;
use PublishPress\Future\Modules\Workflows\Migrations\V040500OnScheduledStepsSchema;
use PublishPress\Future\Modules\Debug\Migrations\V04905DebugLogRequestId;
use PublishPress\Future\Modules\Debug\Migrations\V04906DebugLogTimestampMilliseconds;
use Throwable;

defined('ABSPATH') or die('Direct access not allowed.');

class Plugin implements InitializableInterface
{
    public const LOG_PREFIX = '[Plugin]';

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var ModuleInterface[]
     */
    private $modules;

    /**
     * @var object
     */
    private $legacyPlugin;

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var string
     */
    private $pluginSlug;

    /**
     * @var NoticeInterface
     */
    private $notices;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ModuleInterface[] $modules
     * @param object $legacyPlugin
     * @param HookableInterface $hooksFacade
     * @param string $pluginSlug
     * @param string $basePath
     * @param NoticeInterface $notices
     * @param LoggerInterface $logger
     */
    public function __construct(
        $modules,
        $legacyPlugin,
        HookableInterface $hooksFacade,
        $pluginSlug,
        $basePath,
        NoticeInterface $notices,
        LoggerInterface $logger
    ) {
        $this->modules = $modules;
        $this->legacyPlugin = $legacyPlugin;
        $this->hooks = $hooksFacade;
        $this->basePath = $basePath;
        $this->pluginSlug = $pluginSlug;
        $this->notices = $notices;
        $this->logger = $logger;
    }

    public function initialize()
    {
        $this->initialized = true;

        $this->initializeReviews();
        $this->initializeHooks();
        $this->initializeNotices();
        $this->initializeModules();
        $this->initializeCli();

        $this->hooks->doAction(HooksAbstract::ACTION_AFTER_INIT_PLUGIN);
    }

    private function initializeReviews()
    {
        \PostExpirator_Reviews::init();

        $this->logger->debug(self::LOG_PREFIX . ' Reviews module has been initialized successfully.');
    }

    private function initializeCli()
    {
        if (! defined('WP_CLI')) {
            return;
        }

        \PostExpirator_Cli::getInstance();

        $this->logger->debug(self::LOG_PREFIX . ' CLI module has been initialized successfully.');
    }

    private function initializeHooks()
    {
        $this->hooks->addAction(HooksAbstract::ACTION_INIT, [$this, 'manageUpgrade'], 99);
        $this->hooks->doAction(HooksAbstract::ACTION_INIT_PLUGIN);
        $this->hooks->addAction(
            HooksAbstract::ACTION_ADMIN_ENQUEUE_SCRIPTS,
            [$this, 'initializeI18nForScripts'],
            5
        );
    }

    private function initializeNotices()
    {
        $this->notices->init();

        $this->logger->debug(self::LOG_PREFIX . ' Notices module has been initialized successfully.');
    }

    private function initializeModules()
    {
        foreach ($this->modules as $module) {
            if (method_exists($module, 'initialize')) {
                $module->initialize();
                $this->logger->debug(self::LOG_PREFIX . ' ' . get_class($module) . ' module has been initialized successfully.');
            }
        }
    }

    /**
     * This method is static because it is called before the plugin is initialized.
     * @return void
     */
    public static function onActivate()
    {
        /**
         * Callbacks hooked to this action can't be defined in callbacks of other actions like
         * `plugins_loaded` or `init` because this hook will be executed before those actions.
         */
        do_action(HooksAbstract::ACTION_ACTIVATE_PLUGIN);

        // Full DB repair runs on the next request in manageUpgrade() once the container is loaded.
        update_option('pp_future_pending_db_schema_repair', '1', false);

        SettingsFacade::setDefaultSettings();

        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules -- Needed during plugin activation for rewrite rules
        flush_rewrite_rules();

        // Set flag to redirect to the settings page after activation
        set_transient(PluginsListController::TRANSIENT_REDIRECT_AFTER_ACTIVATION, true, 60);
    }

    public static function onDeactivate()
    {
        do_action(HooksAbstract::ACTION_DEACTIVATE_PLUGIN);

        // phpcs:ignore WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rewrite_rules_flush_rewrite_rules -- Needed during plugin deactivation for rewrite rules
        flush_rewrite_rules();
    }

    public function manageUpgrade()
    {
        try {
            $container = Container::getInstance();

            $this->runPendingDatabaseSchemaRepair($container);

            // Check for current version, if not exists, run activation
            $version = get_option('postexpiratorVersion');

            if ($version === false) {
                // Fresh install. Run migrations that create the DB tables.
                $container->get(ServicesAbstract::HOOKS)->doAction(V30000ActionArgsSchema::HOOK);
                $container->get(ServicesAbstract::HOOKS)->doAction(V40000WorkflowScheduledStepsSchema::HOOK);
                $container->get(ServicesAbstract::HOOKS)->doAction(V040500OnScheduledStepsSchema::HOOK);
            } else {
                if (version_compare($version, '1.6.1') === -1) {
                    update_option('expirationdateDefaultDate', POSTEXPIRATOR_EXPIREDEFAULT);
                }

                if (version_compare($version, '2.0.0-rc1') === -1) {
                    global $wpdb;

                    // Schedule Events/Migrate Config
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
                    $results = $wpdb->get_results(
                        $wpdb->prepare(
                            'select post_id, meta_value from ' . $wpdb->postmeta . ' as postmeta, ' . $wpdb->posts . ' as posts where postmeta.post_id = posts.ID AND postmeta.meta_key = %s AND postmeta.meta_value >= %d',
                            'expiration-date',
                            time()
                        )
                    );

                    foreach ($results as $result) {
                        $opts = [];
                        $posttype = get_post_type($result->post_id);
                        if ($posttype === 'page') {
                            $opts['expireType'] = strtolower(get_option('expirationdateExpiredPageStatus', 'Draft'));
                        } else {
                            $opts['expireType'] = strtolower(get_option('expirationdateExpiredPostStatus', 'Draft'));
                        }

                        $cat = get_post_meta($result->post_id, PostMetaAbstract::EXPIRATION_TERMS, true);
                        if ((isset($cat) && ! empty($cat))) {
                            $opts['category'] = $cat;
                            $opts['expireType'] = 'category';
                        }

                        $this->hooks->doAction(
                            ExpiratorHooks::ACTION_SCHEDULE_POST_EXPIRATION,
                            $result->post_id,
                            $result->meta_value,
                            $opts
                        );
                    }

                    // update meta key to new format
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
                    $wpdb->query(
                        $wpdb->prepare(
                            "UPDATE $wpdb->postmeta SET meta_key = %s WHERE meta_key = %s",
                            PostMetaAbstract::EXPIRATION_TIMESTAMP,
                            'expiration-date'
                        )
                    );

                    // migrate defaults
                    $pagedefault = get_option('expirationdateExpiredPageStatus');
                    $postdefault = get_option('expirationdateExpiredPostStatus');
                    if ($pagedefault) {
                        update_option('expirationdateDefaultsPage', array('expireType' => $pagedefault));
                    }
                    if ($postdefault) {
                        update_option('expirationdateDefaultsPost', array('expireType' => $postdefault));
                    }

                    delete_option('expirationdateCronSchedule');
                    delete_option('expirationdateAutoEnabled');
                    delete_option('expirationdateExpiredPageStatus');
                    delete_option('expirationdateExpiredPostStatus');
                }

                if (version_compare($version, '2.0.1') === -1) {
                    // Forgot to do this in 2.0.0
                    if (is_multisite()) {
                        global $current_blog;
                        wp_clear_scheduled_hook('expirationdate_delete_' . $current_blog->blog_id);
                    } else {
                        wp_clear_scheduled_hook('expirationdate_delete');
                    }
                }

                if (version_compare($version, '3.0.0') === -1) {
                    // TODO: DB migration should probably check a database version option and not the plugin version.
                    $container->get(ServicesAbstract::HOOKS)->doAction(V30000ActionArgsSchema::HOOK);
                    $container->get(ServicesAbstract::HOOKS)->doAction(V30000ReplaceFooterPlaceholders::HOOK);
                    $container->get(ServicesAbstract::CRON)->enqueueAsyncAction(V30000WPCronToActionsScheduler::HOOK, [], true);
                }

                if (version_compare($version, '3.0.1') === -1) {
                    if (! get_option('pp_future_V30001RestorePostMeta')) {
                        $container->get(ServicesAbstract::CRON)->enqueueAsyncAction(
                            V30001RestorePostMeta::HOOK,
                            [],
                            true
                        );

                        update_option('pp_future_V30001RestorePostMeta', true);
                    }
                }

                if (version_compare($version, '3.1.4') === -1) {
                    if (! get_option('pp_future_V30104ArgsColumnLength')) {
                        $container->get(ServicesAbstract::CRON)->enqueueAsyncAction(
                            V30104ArgsColumnLength::HOOK,
                            [],
                            true
                        );

                        update_option('pp_future_V30104ArgsColumnLength', true);
                    }
                }

                if (version_compare($version, '4.0.0', '<')) {
                    $container->get(ServicesAbstract::HOOKS)->doAction(
                        V40000WorkflowScheduledStepsSchema::HOOK
                    );
                }

                if (version_compare($version, '4.5.0', '<')) {
                    $container->get(ServicesAbstract::HOOKS)->doAction(
                        V040500OnScheduledStepsSchema::HOOK
                    );
                }

                if (version_compare($version, '4.10.0', '<')) {
                    $container->get(ServicesAbstract::HOOKS)->doAction(
                        V04905DebugLogRequestId::HOOK
                    );
                    $container->get(ServicesAbstract::HOOKS)->doAction(
                        V04906DebugLogTimestampMilliseconds::HOOK
                    );
                }
            }

            $this->hooks->doAction(HooksAbstract::ACTION_UPGRADE_PLUGIN, $version);

            $currentVersion = $container->get(ServicesAbstract::PLUGIN_VERSION);
            if ($version !== $currentVersion) {
                update_option('postexpiratorVersion', $currentVersion);
            }
        } catch (Throwable $th) {
            $this->logger->error('Error managing upgrade: ' . $th->getMessage());
        }
    }

    /**
     * Runs after plugin activation (see install.php) once the container is available.
     */
    private function runPendingDatabaseSchemaRepair(Container $container): void
    {
        $pending = get_option('pp_future_pending_db_schema_repair');
        if (empty($pending)) {
            return;
        }

        delete_option('pp_future_pending_db_schema_repair');

        try {
            $container->get(ServicesAbstract::DATABASE_SCHEMA_MAINTAINER)->repairAllSchemas();
        } catch (Throwable $th) {
            update_option('pp_future_pending_db_schema_repair', '1', false);
            $this->logger->error(
                self::LOG_PREFIX . ' Pending database schema repair failed: ' . $th->getMessage()
            );
        }
    }

    public static function getScriptUrl(string $script): string
    {
        $extension = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '.js' : '.min.js';

        return self::getAssetUrl('js/' . $script . $extension);
    }

    /**
     * @since 4.3.1
     */
    public static function getAssetUrl(string $asset): string
    {
        $container = Container::getInstance();
        $baseUrl = $container->get(ServicesAbstract::BASE_URL);

        return $baseUrl . 'assets/' . $asset;
    }

    public function initializeI18nForScripts()
    {
        wp_enqueue_script(
            'publishpress-i18n',
            self::getScriptUrl('i18n'),
            [
                'wp-i18n',
            ],
            PUBLISHPRESS_FUTURE_VERSION,
            true
        );

        $json = $this->getLocalizedTranslations();

        wp_localize_script(
            'publishpress-i18n',
            'publishpressI18nConfig',
            [
                'data' => $json,
            ]
        );
    }

    private function getLocalizedTranslations()
    {
        $currentLocale = get_locale();
        $jsonPath = $this->basePath . '/languages/post-expirator-' . $currentLocale . '.json';

        if (! file_exists($jsonPath)) {
            return [];
        }

        // phpcs:ignore WordPressVIPMinimum.Performance.FetchingRemoteData.FileGetContentsUnknown
        $json = file_get_contents($jsonPath);
        return json_decode($json, true);
    }
}
