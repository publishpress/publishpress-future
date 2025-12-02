<?php

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Expirator\Migrations\V30000WPCronToActionsScheduler;
use PublishPress\Future\Modules\Expirator\Migrations\V30001RestorePostMeta;
use PublishPress\Future\Modules\Settings\HooksAbstract as SettingsHooksAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooksAbstract;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;
use PublishPress\Future\Modules\Settings\SettingsFacade;

defined('ABSPATH') or die('Direct access not allowed.');

/**
 * The class that is responsible for all the displays.
 */
// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps
class PostExpirator_Display
{
    /**
     * The singleton instance.
     */
    private static $instance = null;

    /**
     * @var \PublishPress\Future\Modules\Expirator\Interfaces\CronInterface
     */
    private $cron;

    /**
     * @var PublishPress\Future\Core\HookableInterface
     */
    private $hooks;

    /**
     * @var DBTableSchemaInterface
     */
    private $actionArgsSchema;

    /**
     * @var DBTableSchemaInterface
     */
    private $debugLogSchema;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    /**
     * Constructor.
     */
    private function __construct()
    {
        $container = Container::getInstance();

        $this->cron = $container->get(ServicesAbstract::CRON);
        $this->hooks = $container->get(ServicesAbstract::HOOKS);
        $this->actionArgsSchema = $container->get(ServicesAbstract::DB_TABLE_ACTION_ARGS_SCHEMA);
        $this->debugLogSchema = $container->get(ServicesAbstract::DB_TABLE_DEBUG_LOG_SCHEMA);
        $this->settingsFacade = $container->get(ServicesAbstract::SETTINGS);

        $this->hooks();
    }

    /**
     * Initialize the hooks.
     */
    private function hooks()
    {
        $this->hooks->addAction('init', [$this, 'init']);
    }

    /**
     * Returns instance of the singleton.
     */
    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init()
    {
    }

    /**
     * Loads the specified tab.
     *
     * Make sure the name of the file is menu_{$tab}.php.
     */
    public function load_tab($tab)
    {
        $function = 'menu_' . $tab;

        if (method_exists($this, $function)) {
            $this->$function();
        }

        $this->hooks->doAction(SettingsHooksAbstract::ACTION_LOAD_TAB, $tab);
    }

    /**
     * Creates the settings page.
     */
    public function future_actions_tabs()
    {
        if (!is_admin() || !current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to configure PublishPress Future.', 'post-expirator'));
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';

        $allowed_tabs = ['defaults', 'general', 'display', 'admin', 'notifications', ];
        $allowed_tabs = $this->hooks->applyFilters(SettingsHooksAbstract::FILTER_ALLOWED_TABS, $allowed_tabs);

        if (empty($tab) || ! in_array($tab, $allowed_tabs, true)) {
            $tab = 'defaults';
        }

        ob_start();
        $this->load_tab($tab);
        $html = ob_get_clean();

        $this->render_template('tabs-future-actions', ['tabs' => $allowed_tabs, 'html' => $html, 'tab' => $tab]);

        $this->publishpress_footer();
    }

    /**
     * Creates the settings page.
     */
    public function settings_tabs()
    {
        if (!is_admin() || !current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to configure PublishPress Future.', 'post-expirator'));
        }

        $defaultTab = $this->settingsFacade->getSettingsDefaultTab();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : $defaultTab;

        $allowed_tabs = ['advanced', 'diagnostics', 'viewdebug' ];
        $allowed_tabs = $this->hooks->applyFilters(SettingsHooksAbstract::FILTER_ALLOWED_SETTINGS_TABS, $allowed_tabs);

        $debugIsEnabled = (bool)$this->hooks->applyFilters(SettingsHooksAbstract::FILTER_DEBUG_ENABLED, false);
        if (! $debugIsEnabled) {
            unset($allowed_tabs['viewdebug']);

            if ($tab === 'viewdebug') {
                wp_die(esc_html__('Debug is disabled', 'post-expirator'));
            }
        }

        if (empty($tab) || ! in_array($tab, $allowed_tabs, true)) {
            $tab = $this->settingsFacade::SETTINGS_DEFAULT_TAB;
        }

        ob_start();
        $this->load_tab($tab);
        $html = ob_get_clean();

        $this->render_template('tabs-settings', ['tabs' => $allowed_tabs, 'html' => $html, 'tab' => $tab]);

        $this->publishpress_footer();
    }

    private function menu_defaults()
    {
        $params = [
            'showSideBar' => $this->hooks->applyFilters(
                SettingsHooksAbstract::FILTER_SHOW_PRO_BANNER,
                ! defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')
            ),
        ];

        $this->render_template('menu-defaults', $params);
    }

    /**
     * Display menu.
     */
    private function menu_display()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (isset($_POST['expirationdateSaveDisplay']) && sanitize_key($_POST['expirationdateSaveDisplay'])) {
            if (
                ! isset($_POST['_postExpiratorMenuDisplay_nonce']) || ! wp_verify_nonce(
                    sanitize_key($_POST['_postExpiratorMenuDisplay_nonce']),
                    'postexpirator_menu_display'
                )
            ) {
                print 'Form Validation Failure: Sorry, your nonce did not verify.';
                exit;
            } else {
                // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
                $this->settingsFacade->setDefaultDateFormat(sanitize_text_field($_POST['expired-default-date-format']));
                $this->settingsFacade->setDefaultTimeFormat(sanitize_text_field($_POST['expired-default-time-format']));
                $this->settingsFacade->setShowInPostFooter((bool)$_POST['expired-display-footer']);
                $this->settingsFacade->setFooterContents(wp_kses($_POST['expired-footer-contents'], []));
                $this->settingsFacade->setFooterStyle(wp_kses($_POST['expired-footer-style'], []));
                $this->settingsFacade->setShortcodeWrapper(sanitize_text_field($_POST['shortcode-wrapper']));
                $this->settingsFacade->setShortcodeWrapperClass(sanitize_text_field($_POST['shortcode-wrapper-class']));
                // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            }
        }

        $params = [
            'showSideBar' => $this->hooks->applyFilters(
                SettingsHooksAbstract::FILTER_SHOW_PRO_BANNER,
                ! defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')
            ),
        ];

        $this->render_template('menu-display', $params);
    }

    /**
     * Admn menu.
     */
    private function menu_admin()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        if (isset($_POST['expirationdateSaveAdmin']) && sanitize_key($_POST['expirationdateSaveAdmin'])) {
            if (
                ! isset($_POST['_postExpiratorMenuAdmin_nonce']) || ! wp_verify_nonce(
                    sanitize_key($_POST['_postExpiratorMenuAdmin_nonce']),
                    'postexpirator_menu_admin'
                )
            ) {
                print 'Form Validation Failure: Sorry, your nonce did not verify.';
                exit;
            } else {
                // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
                $this->settingsFacade->setColumnStyle(sanitize_key($_POST['future-action-column-style']));
                $this->settingsFacade->setTimeFormatForDatePicker(sanitize_key($_POST['future-action-time-format']));
                $this->settingsFacade->setMetaboxTitle(sanitize_text_field($_POST['expirationdate-metabox-title']));
                $this->settingsFacade->setMetaboxCheckboxLabel(sanitize_text_field($_POST['expirationdate-metabox-checkbox-label']));
                // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            }
        }

        $params = [
            'showSideBar' => $this->hooks->applyFilters(
                SettingsHooksAbstract::FILTER_SHOW_PRO_BANNER,
                ! defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')
            ),
        ];

        $this->render_template('menu-admin', $params);
    }

    /**
     * Diagnostics menu.
     */
    private function menu_diagnostics()
    {
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            if (
                ! isset($_POST['_postExpiratorMenuDiagnostics_nonce']) || ! wp_verify_nonce(
                    sanitize_key($_POST['_postExpiratorMenuDiagnostics_nonce']),
                    'postexpirator_menu_diagnostics'
                )
            ) {
                print 'Form Validation Failure: Sorry, your nonce did not verify.';
                exit;
            }
            if (isset($_POST['debugging-disable'])) {
                update_option('expirationdateDebug', 0);
                echo "<div id='message' class='updated fade'><p>";
                esc_html_e('Debugging Disabled', 'post-expirator');
                echo '</p></div>';
            } elseif (isset($_POST['debugging-enable'])) {
                update_option('expirationdateDebug', 1);
                echo "<div id='message' class='updated fade'><p>";
                esc_html_e('Debugging Enabled', 'post-expirator');
                echo '</p></div>';
            } elseif (isset($_POST['purge-debug'])) {
                require_once POSTEXPIRATOR_LEGACYDIR . '/debug.php';

                $debug = new PostExpiratorDebug();
                $debug->purge();
                echo "<div id='message' class='updated fade'><p>";
                esc_html_e('Debugging Table Emptied', 'post-expirator');
                echo '</p></div>';
            } elseif (isset($_POST['migrate-legacy-actions'])) {
                $this->cron->enqueueAsyncAction(V30000WPCronToActionsScheduler::HOOK, [], true);

                echo "<div id='message' class='updated fade'><p>";
                esc_html_e(
                    'The legacy future actions migration has been enqueued and will run asynchronously.',
                    'post-expirator'
                );
                echo '</p></div>';
            } elseif (isset($_POST['restore-post-meta'])) {
                $this->cron->enqueueAsyncAction(V30001RestorePostMeta::HOOK, [], true);

                echo "<div id='message' class='updated fade'><p>";
                esc_html_e(
                    'The legacy actions arguments restoration has been enqueued and will run asynchronously.',
                    'post-expirator'
                );
                echo '</p></div>';
            } elseif (isset($_POST['fix-db-schema'])) {
                $this->actionArgsSchema->fixTable();
                $this->debugLogSchema->fixTable();
                $this->hooks->doAction(SettingsHooksAbstract::ACTION_FIX_DB_SCHEMA);

                $schemaIsHealthy = $this->actionArgsSchema->isTableHealthy() && $this->debugLogSchema->isTableHealthy();
                $schemaIsHealthy = $this->hooks->applyFilters(SettingsHooksAbstract::FILTER_SCHEMA_IS_HEALTHY, $schemaIsHealthy);

                echo "<div id='message' class='updated fade'><p>";
                if ($schemaIsHealthy) {
                    esc_html_e(
                        'The database schema was fixed.',
                        'post-expirator'
                    );
                } else {
                    esc_html_e(
                        'The database schema could not be fixed. Please, contact the support team.',
                        'post-expirator'
                    );
                }

                echo '</p></div>';
            }
        }

        $params = [
            'showSideBar' => $this->hooks->applyFilters(
                SettingsHooksAbstract::FILTER_SHOW_PRO_BANNER,
                ! defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')
            ),
        ];

        $this->render_template('menu-diagnostics', $params);
    }

    /**
     * Debug menu.
     */
    private function menu_viewdebug()
    {
        require_once POSTEXPIRATOR_LEGACYDIR . '/debug.php';

        $params = [
            'showSideBar' => $this->hooks->applyFilters(
                SettingsHooksAbstract::FILTER_SHOW_PRO_BANNER,
                ! defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')
            ),
        ];

        $this->render_template('menu-debug-log', $params);
    }

    /**
     * Show the Expiration Date options page
     */
    private function menu_general()
    {
        if (isset($_POST['expirationdateSave']) && ! empty($_POST['expirationdateSave'])) {
            if (
                ! isset($_POST['_postExpiratorMenuGeneral_nonce']) || ! wp_verify_nonce(
                    sanitize_key($_POST['_postExpiratorMenuGeneral_nonce']),
                    'postexpirator_menu_general'
                )
            ) {
                print 'Form Validation Failure: Sorry, your nonce did not verify.';
                exit;
            } else {
                // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
                $this->settingsFacade->setGeneralDateTimeOffset(
                    sanitize_text_field($_POST['expired-custom-expiration-date'])
                );

                $this->settingsFacade->setHideCalendarByDefault(
                    isset($_POST['expired-hide-calendar-by-default']) && $_POST['expired-hide-calendar-by-default'] == '1'
                );
                // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotValidated

                if (! isset($_POST['allow-user-roles']) || ! is_array($_POST['allow-user-roles'])) {
                    $_POST['allow-user-roles'] = [];
                }

                $allowUserRoles = array_map('sanitize_text_field', $_POST['allow-user-roles']);

                $this->settingsFacade->setAllowUserRoles($allowUserRoles);

                echo "<div id='message' class='updated fade'><p>";
                esc_html_e('Saved Options!', 'post-expirator');
                echo '</p></div>';
            }
        }

        $params = [
            'showSideBar' => $this->hooks->applyFilters(
                SettingsHooksAbstract::FILTER_SHOW_PRO_BANNER,
                ! defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')
            ),
        ];

        $this->render_template('menu-general', $params);
    }

    private function menu_notifications()
    {
        if (isset($_POST['expirationNotificationSave']) && ! empty($_POST['expirationNotificationSave'])) {
            if (
                ! isset($_POST['_postExpiratorMenuNotifications_nonce']) || ! wp_verify_nonce(
                    sanitize_key($_POST['_postExpiratorMenuNotifications_nonce']),
                    'postexpirator_menu_notifications'
                )
            ) {
                throw new \Exception('The nonce did not verify.');
            }

            if (! isset($_POST['expired-email-notification-list'])) {
                throw new \Exception('The expired email notification list is not set.');
            }

            if (! isset($_POST['past-due-actions-notification-list'])) {
                throw new \Exception('The past due actions notification list is not set.');
            }

            $expiredEmailNotificationList = explode(',', sanitize_text_field($_POST['expired-email-notification-list']));
            $pastDueActionsNotificationList = explode(',', sanitize_text_field($_POST['past-due-actions-notification-list']));

            $expiredEmailNotificationList = array_map('trim', $expiredEmailNotificationList);
            $expiredEmailNotificationList = array_filter($expiredEmailNotificationList, 'is_email');

            $pastDueActionsNotificationList = array_map('trim', $pastDueActionsNotificationList);
            $pastDueActionsNotificationList = array_filter($pastDueActionsNotificationList, 'is_email');


            // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
            $this->settingsFacade->setSendEmailNotification((bool)$_POST['expired-email-notification']);
            $this->settingsFacade->setSendEmailNotificationToAdmins((bool)$_POST['expired-email-notification-admins']);
            $this->settingsFacade->setEmailNotificationAddressesList($expiredEmailNotificationList);
            $this->settingsFacade->setPastDueActionsNotificationStatus((bool)$_POST['past-due-actions-notification']);
            $this->settingsFacade->setPastDueActionsNotificationAddressesList($pastDueActionsNotificationList);
            // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotValidated

            echo "<div id='message' class='updated fade'><p>";
            esc_html_e('Saved Options!', 'post-expirator');
            echo '</p></div>';
        }

        $params = [
            'showSideBar' => $this->hooks->applyFilters(
                SettingsHooksAbstract::FILTER_SHOW_PRO_BANNER,
                ! defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')
            ),
        ];

        $this->render_template('menu-notifications', $params);
    }

    /**
     * Show the Expiration Date options page
     */
    private function menu_advanced()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (isset($_GET['settings-updated']) && $_GET['settings-updated'] === 'true') {
            echo "<div id='message' class='updated fade'><p>";
            esc_html_e('Saved Options!', 'post-expirator');
            echo '</p></div>';
        }

        $params = [
            'showSideBar' => $this->hooks->applyFilters(
                SettingsHooksAbstract::FILTER_SHOW_PRO_BANNER,
                ! defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')
            ),
        ];

        $this->render_template('menu-advanced', $params);
    }

    /**
     * Renders a named template, if it is found.
     */
    public function render_template($name, $params = null)
    {
        /**
         * Allows changing template parameters.
         * @param null|array<string,mixed> $params
         * @param string $name
         * @return null|array<string,mixed>
         */
        $params = $this->hooks->applyFilters(
            ExpiratorHooksAbstract::FILTER_LEGACY_TEMPLATE_PARAMS,
            $params,
            $name
        );

        /**
         * Allows changing the template file name.
         * @param string $template
         * @param string $name
         * @param null|array<string,mixed> $params
         * @return null|array<string,mixed>
         */
        $template = $this->hooks->applyFilters(
            ExpiratorHooksAbstract::FILTER_LEGACY_TEMPLATE_FILE,
            POSTEXPIRATOR_BASEDIR . "/src/Views/{$name}.php",
            $name,
            $params
        );

        $allowedBasePath = realpath(POSTEXPIRATOR_BASEDIR . "/src/Views/");
        $templatePath = realpath($template);

        if ($templatePath === false || strpos($templatePath, $allowedBasePath) !== 0) {
            return;
        }

        if (file_exists($template)) {
            // expand all parameters so that they can be directly accessed with their name.
            if ($params) {
                foreach ($params as $param => $value) {
                    $$param = $value;
                }
            }
            include $template;
        }
    }

    /**
     * Returns the rendered template
     *
     * @param string $name
     * @param array $param
     * @return string
     */
    public function get_rendered_template($name, $params = null)
    {
        ob_start();

        $this->render_template($name, $params);

        return ob_get_clean();
    }

    /**
     * PublishPress footer
     */
    public function publishpress_footer()
    {
        ?>
        <footer>
            <div class="pp-rating">
                <a href="https://wordpress.org/support/plugin/post-expirator/reviews/#new-post" target="_blank"
                   rel="noopener noreferrer">
                    <?php
                    printf(
                        // translators: %1$s is the plugin name, %2$s is the star rating markup
                        esc_html__('If you like %1$s, please leave us a %2$s rating. Thank you!', 'post-expirator'),
                        '<strong>PublishPress Future</strong>',
                        '<span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span><span class="dashicons dashicons-star-filled"></span>'
                    );
                    ?>
                </a>
            </div>

            <hr>

            <nav>
                <ul>
                    <li>
                        <a href="https://publishpress.com/future/" target="_blank" rel="noopener noreferrer"
                           title="<?php
                            esc_attr_e('About PublishPress Future', 'post-expirator'); ?>">
                            <?php
                            esc_html_e('About', 'post-expirator'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="https://publishpress.com/knowledge-base/future-introduction/" target="_blank"
                           rel="noopener noreferrer" title="<?php
                            esc_attr_e('Future Documentation', 'post-expirator'); ?>">
                            <?php
                            esc_html_e('Documentation', 'post-expirator'); ?>
                        </a>
                    </li>
                    <li>
                        <a href="https://publishpress.com/publishpress-support/" target="_blank" rel="noopener noreferrer"
                           title="<?php
                            esc_attr_e('Contact the PublishPress team', 'post-expirator'); ?>">
                            <?php
                            esc_html_e('Contact', 'post-expirator'); ?>
                        </a>
                    </li>
                </ul>
            </nav>

            <div class="pp-pressshack-logo">
                <a href="https://publishpress.com" target="_blank" rel="noopener noreferrer">
                    <img src="<?php
                    echo esc_url(plugins_url('../assets/images/publishpress-logo.png', dirname(__FILE__))) ?>"/>
                </a>
            </div>
        </footer>
        <?php
    }
}
