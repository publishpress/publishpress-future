<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Controllers;

use PostExpirator_Display;
use PostExpirator_Facade;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Core\HooksAbstract as CoreHooksAbstract;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use PublishPress\Future\Modules\Expirator\Models\CurrentUserModel;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use Throwable;

defined('ABSPATH') or die('Direct access not allowed.');

class QuickEditController implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var CurrentUserModel
     */
    private $currentUserModel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    /**
     * @param HookableInterface $hooksFacade
     */
    public function __construct(
        HookableInterface $hooksFacade,
        \Closure $currentUserModelFactory,
        LoggerInterface $logger,
        SettingsFacade $settingsFacade
    ) {
        $this->hooks = $hooksFacade;
        $this->currentUserModel = $currentUserModelFactory();
        $this->logger = $logger;
        $this->settingsFacade = $settingsFacade;
    }

    public function initialize()
    {
        if (! $this->currentUserModel->userCanExpirePosts()) {
            return;
        }

        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_QUICK_EDIT_CUSTOM_BOX,
            [$this, 'registerQuickEditCustomBox'],
            10,
            2
        );

        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_SAVE_POST,
            [$this, 'processQuickEditUpdate']
        );

        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_ADMIN_PRINT_SCRIPTS_EDIT,
            [$this, 'enqueueScripts']
        );
    }

    public function registerQuickEditCustomBox($columnName, $postType)
    {
        try {
            if ($columnName !== 'expirationdate') {
                return;
            }

            if (! $this->isEnabledForPostType($postType)) {
                return;
            }

            $container = Container::getInstance();
            $settingsFacade = $container->get(ServicesAbstract::SETTINGS);

            $defaults = $settingsFacade->getPostTypeDefaults($postType);
            $taxonomy = isset($defaults['taxonomy']) ? $defaults['taxonomy'] : '';
            $label = '';

            // if settings have not been configured and this is the default post type
            if (empty($taxonomy) && 'post' === $postType) {
                $taxonomy = 'category';
            }

            if (! empty($taxonomy)) {
                $tax_object = get_taxonomy($taxonomy);
                $label = $tax_object ? $tax_object->label : '';
            }

            PostExpirator_Display::getInstance()->render_template('quick-edit', array(
                'post_type' => $postType,
                'taxonomy' => $taxonomy,
                'tax_label' => $label
            ));
        } catch (Throwable $th) {
            $this->logger->error('Error registering quick edit custom box: ' . $th->getMessage());
        }
    }

    public function processQuickEditUpdate($postId)
    {
        try {
            // Don't run if this is an auto save
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            // Don't update data if the function is called for saving revision.
            $posttype = get_post_type((int)$postId);
            if ($posttype === 'revision') {
                return;
            }

            if (empty($_POST['future_action_view']) || $_POST['future_action_view'] !== 'quick-edit') {
                return;
            }

            $currentUserModelFactory = Container::getInstance()->get(ServicesAbstract::CURRENT_USER_MODEL_FACTORY);
            $currentUserModel = $currentUserModelFactory();

            if (! $currentUserModel->userCanExpirePosts()) {
                return;
            }

            check_ajax_referer('__future_action', '_future_action_nonce');



            // Classic editor, quick edit
            $shouldSchedule = isset($_POST['future_action_enabled']) && $_POST['future_action_enabled'] === '1';

            if ($shouldSchedule) {
                $expireType = isset($_POST['future_action_action']) ? sanitize_text_field($_POST['future_action_action']) : '';
                $newStatus = isset($_POST['future_action_new_status']) ? sanitize_text_field($_POST['future_action_new_status']) : 'draft';

                if ($expireType === ExpirationActionsAbstract::POST_STATUS_TO_DRAFT) {
                    $expireType = ExpirationActionsAbstract::CHANGE_POST_STATUS;
                    $newStatus = 'draft';
                }

                if ($expireType === ExpirationActionsAbstract::POST_STATUS_TO_PRIVATE) {
                    $expireType = ExpirationActionsAbstract::CHANGE_POST_STATUS;
                    $newStatus = 'private';
                }

                if ($expireType === ExpirationActionsAbstract::POST_STATUS_TO_TRASH) {
                    $expireType = ExpirationActionsAbstract::CHANGE_POST_STATUS;
                    $newStatus = 'trash';
                }

                $opts = [
                    'expireType' => $expireType,
                    'newStatus' => $newStatus,
                    'category' => isset($_POST['future_action_terms']) ? sanitize_text_field($_POST['future_action_terms']) : '',
                    'categoryTaxonomy' => isset($_POST['future_action_taxonomy']) ? sanitize_text_field($_POST['future_action_taxonomy']) : '',
                ];

                if (! empty($opts['category'])) {
                    $taxonomiesModelFactory = Container::getInstance()->get(ServicesAbstract::TAXONOMIES_MODEL_FACTORY);
                    $taxonomiesModel = $taxonomiesModelFactory();

                    $opts['category'] = $taxonomiesModel->normalizeTermsCreatingIfNecessary(
                        $opts['categoryTaxonomy'],
                        explode(',', $opts['category'])
                    );
                }

                $date = isset($_POST['future_action_date']) ? sanitize_text_field($_POST['future_action_date']) : '0';
                $date = strtotime($date);

                $this->hooks->doAction(ExpiratorHooks::ACTION_SCHEDULE_POST_EXPIRATION, $postId, $date, $opts);

                return;
            }

            $this->hooks->doAction(ExpiratorHooks::ACTION_UNSCHEDULE_POST_EXPIRATION, $postId);
        } catch (Throwable $th) {
            $this->logger->error('Error processing quick edit update: ' . $th->getMessage());
        }
    }

    private function isEnabledForPostType($postType)
    {
        $container = Container::getInstance();
        $settingsFacade = $container->get(ServicesAbstract::SETTINGS);
        $actionsModel = $container->get(ServicesAbstract::EXPIRATION_ACTIONS_MODEL);

        $postTypeDefaultConfig = $settingsFacade->getPostTypeDefaults($postType);

        if (! in_array((string)$postTypeDefaultConfig['activeMetaBox'], ['active', '1', true])) {
            return false;
        }

        $hideMetabox = (bool)$this->hooks->applyFilters(HooksAbstract::FILTER_HIDE_METABOX, false, $postType);
        if ($hideMetabox) {
            return false;
        }

        return true;
    }

    public function enqueueScripts()
    {
        try {
            $currentScreen = get_current_screen();

            if ($currentScreen->base !== 'edit') {
                return;
            }

            $postType = $currentScreen->post_type;

            if (! $this->isEnabledForPostType($postType)) {
                return;
            }

            wp_enqueue_script("wp-components");
            wp_enqueue_script("wp-plugins");
            wp_enqueue_script("wp-element");
            wp_enqueue_script("wp-data");

            wp_enqueue_script(
                'postexpirator-quick-edit',
                Plugin::getScriptUrl('quickEdit'),
                [
                    'wp-i18n',
                    'wp-components',
                    'wp-url',
                    'wp-data',
                    'wp-api-fetch',
                    'wp-element',
                    'inline-edit-post',
                    'wp-html-entities',
                    'wp-plugins',
                ],
                PUBLISHPRESS_FUTURE_VERSION,
                true
            );

            wp_enqueue_style('wp-components');

            $container = Container::getInstance();
            $settingsFacade = $container->get(ServicesAbstract::SETTINGS);
            $actionsModel = $container->get(ServicesAbstract::EXPIRATION_ACTIONS_MODEL);
            $defaultDataModelFactory = $container->get(ServicesAbstract::POST_TYPE_DEFAULT_DATA_MODEL_FACTORY);
            $debug = $container->get(ServicesAbstract::DEBUG);

            $postTypeDefaultConfig = $settingsFacade->getPostTypeDefaults($postType);
            $defaultDataModel = $defaultDataModelFactory->create($postType);

            $taxonomyPluralName = '';
            if (! empty($postTypeDefaultConfig['taxonomy'])) {
                $taxonomy = get_taxonomy($postTypeDefaultConfig['taxonomy']);

                if (! is_wp_error($taxonomy) && ! empty($taxonomy)) {
                    $taxonomyPluralName = $taxonomy->label;
                }
            }

            if (empty($taxonomyPluralName)) {
                $taxonomyPluralName = __('Taxonomy', 'post-expirator');
            }

            $taxonomyTerms = [];
            if (! empty($postTypeDefaultConfig['taxonomy'])) {
                $taxonomyTerms = get_terms([
                    'taxonomy' => $postTypeDefaultConfig['taxonomy'],
                    'hide_empty' => false,
                ]);
            }

            try {
                $defaultExpirationDate = $defaultDataModel->getActionDateParts();
            } catch (Throwable $e) {
                $now = time();
                $gmDate = gmdate('Y-m-d H:i:s', $now);
                $calculatedDate = $now;

                $defaultExpirationDate = [
                    'year' => date('Y', $now),
                    'month' => date('m', $now),
                    'day' => date('d', $now),
                    'hour' => date('H', $now),
                    'minute' => date('i', $now),
                    'ts' => $calculatedDate,
                    'iso' => $gmDate
                ];
            }

            $nonce = wp_create_nonce('__future_action');

            $metaboxTitle = $this->settingsFacade->getMetaboxTitle() ?? __('Future Actions', 'post-expirator');
            $metaboxCheckboxLabel = $this->settingsFacade->getMetaboxCheckboxLabel() ?? __('Enable Future Action', 'post-expirator');

            $hiddenFields = (array) $this->hooks->applyFilters(HooksAbstract::FILTER_HIDDEN_METABOX_FIELDS, [], $postType);

            wp_localize_script(
                'postexpirator-quick-edit',
                'publishpressFutureQuickEditConfig',
                [
                    'postTypeDefaultConfig' => $postTypeDefaultConfig,
                    'defaultDate' => $defaultExpirationDate['iso'],
                    'is12Hour' => get_option('time_format') !== 'H:i',
                    'timeFormat' => $settingsFacade->getTimeFormatForDatePicker(),
                    'startOfWeek' => get_option('start_of_week', 0),
                    'actionsSelectOptions' => $actionsModel->getActionsAsOptions($postType),
                    'statusesSelectOptions' => $actionsModel->getStatusesAsOptionsForPostType($postType),
                    'isDebugEnabled' => $debug->isEnabled(),
                    'taxonomyName' => $taxonomyPluralName,
                    'taxonomyTerms' => $taxonomyTerms,
                    'postType' => $currentScreen->post_type,
                    'isNewPost' => false,
                    'nonce' => $nonce,
                    'hideCalendarByDefault' => $settingsFacade->getHideCalendarByDefault(),
                    'hiddenFields' => $hiddenFields,
                    'wpTimezone' => wp_timezone_string(),
                    'strings' => [
                        'category' => __('Categories', 'post-expirator'),
                        'panelTitle' => $metaboxTitle,
                        'enablePostExpiration' => $metaboxCheckboxLabel,
                        'action' => __('Action', 'post-expirator'),
                        'showCalendar' => __('Show Calendar', 'post-expirator'),
                        'hideCalendar' => __('Hide Calendar', 'post-expirator'),
                        'loading' => __('Loading', 'post-expirator'),
                        // translators: the text between {{}} is the link to the settings page.
                        'timezoneSettingsHelp' => __('Timezone is controlled by the {WordPress Settings}.', 'post-expirator'),
                        // translators: %s is the name of the taxonomy in plural form.
                        'noTermsFound' => sprintf(
                            // translators: %s is the name of the taxonomy in plural form.
                            __('No %s found.', 'post-expirator'),
                            strtolower($taxonomyPluralName)
                        ),
                        'noTaxonomyFound' => __('You must assign a taxonomy to this post type to use this feature.', 'post-expirator'),
                        // translators: %s is the name of the taxonomy in plural form.
                        'newTerms' => __('New %s', 'post-expirator'),
                        // translators: %s is the name of the taxonomy in plural form.
                        'removeTerms' => __('%s to remove', 'post-expirator'),
                        // translators: %s is the name of the taxonomy in plural form.
                        'addTerms' => __('%s to add', 'post-expirator'),
                        // translators: %s is the name of the taxonomy in singular form.
                        'addTermsPlaceholder' => sprintf(__('Search for %s', 'post-expirator'), strtolower($taxonomyPluralName)),
                        'errorActionRequired' => __('Select an action', 'post-expirator'),
                        'errorDateRequired' => __('Select a date', 'post-expirator'),
                        'errorDateInPast' => __('Date cannot be in the past', 'post-expirator'),
                        'errorTermsRequired' => sprintf(
                            // translators: %s is the name of the taxonomy in singular form.
                            __('Please select one or more %s', 'post-expirator'),
                            strtolower($taxonomyPluralName)
                        ),
                        'newStatus' => __('New status', 'post-expirator'),
                    ]
                ]
            );
        } catch (Throwable $th) {
            $this->logger->error('Error enqueuing scripts: ' . $th->getMessage());
        }
    }
}
