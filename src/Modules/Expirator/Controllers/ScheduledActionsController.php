<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Controllers;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Core\HooksAbstract as CoreHooksAbstract;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use PublishPress\Future\Modules\Expirator\Tables\ScheduledActionsTable as ScheduledActionsTable;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use PublishPress\Future\Modules\Workflows\HooksAbstract as WorkflowsHooksAbstract;
use PublishPress\Future\Modules\Workflows\Module;
use Throwable;

defined('ABSPATH') or die('Direct access not allowed.');

class ScheduledActionsController implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;
    /**
     * @var \Closure
     */
    private $actionArgsModelFactory;

    /**
     * @var ScheduledActionsTable
     */
    private $listTable;

    /**
     * @var \Closure
     */
    private $scheduledActionsTableFactory;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param HookableInterface $hooksFacade
     */
    public function __construct(
        HookableInterface $hooksFacade,
        \Closure $actionArgsModelFactory,
        \Closure $scheduledActionsTableFactory,
        SettingsFacade $settingsFacade,
        LoggerInterface $logger
    ) {
        $this->hooks = $hooksFacade;
        $this->actionArgsModelFactory = $actionArgsModelFactory;
        $this->scheduledActionsTableFactory = $scheduledActionsTableFactory;
        $this->settingsFacade = $settingsFacade;
        $this->logger = $logger;
    }

    public function initialize()
    {
        $this->hooks->addAction(
            HooksAbstract::ACTION_ADMIN_MENU,
            [$this, 'onAdminMenu']
        );
        $this->hooks->addAction(
            HooksAbstract::ACTION_SCHEDULER_DELETED_ACTION,
            [$this, 'onActionSchedulerDeletedAction']
        );
        $this->hooks->addAction(
            HooksAbstract::ACTION_SCHEDULER_CANCELED_ACTION,
            [$this, 'onActionSchedulerDisableAction']
        );
        $this->hooks->addAction(
            HooksAbstract::ACTION_SCHEDULER_AFTER_EXECUTE,
            [$this, 'onActionSchedulerDisableAction']
        );
        $this->hooks->addAction(
            HooksAbstract::ACTION_SCHEDULER_FAILED_EXECUTION,
            [$this, 'onActionSchedulerDisableAction']
        );
        $this->hooks->addFilter('screen_settings', [$this, 'filterScreenSettings'], 10, 2);
        $this->hooks->addFilter('set-screen-option', [$this, 'filterSetScreenOption'], 10);

        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_ADMIN_ENQUEUE_SCRIPTS,
            [$this, 'enqueueScripts']
        );

        $this->hooks->addFilter(
            HooksAbstract::FILTER_ACTION_SCHEDULER_ADMIN_NOTICE,
            [$this, 'filterActionSchedulerAdminNotice'],
            10,
            3
        );

        $this->hooks->addFilter(
            CoreHooksAbstract::FILTER_ADMIN_TITLE,
            [$this, 'onFilterAdminTitle'],
            10,
            2
        );
    }

    public function onAdminMenu()
    {
        try {
            add_menu_page(
                __('PublishPress Future', 'post-expirator'),
                __('Future', 'post-expirator'),
                'manage_options',
                'publishpress-future',
                [\PostExpirator_Display::getInstance(), 'future_actions_tabs'],
                'dashicons-clock',
                74
            );

            add_submenu_page(
                'publishpress-future',
                __('Future Actions', 'post-expirator'),
                __('Future Actions', 'post-expirator'),
                'manage_options',
                'publishpress-future',
                [\PostExpirator_Display::getInstance(), 'future_actions_tabs']
            );

            $hook_suffix = add_submenu_page(
                "edit.php?post_type=" . Module::POST_TYPE_WORKFLOW,
                __('Scheduled Actions', 'post-expirator'),
                __('Scheduled Actions', 'post-expirator'),
                'manage_options',
                'publishpress-future-scheduled-actions',
                [$this, 'renderScheduledActionsTemplate']
            );
            $this->hooks->addAction('load-' . $hook_suffix, [$this, 'processAdminUi']);
        } catch (Throwable $th) {
            $this->logger->error('Error adding scheduled actions menu: ' . $th->getMessage());
        }
    }

    public function renderScheduledActionsTemplate()
    {
        $table = $this->getListTable();
        $table->display_page();

        \PostExpirator_Display::getInstance()->publishpress_footer();
    }

    public function onActionSchedulerDeletedAction($actionId)
    {
        $factory = $this->actionArgsModelFactory;
        $actionArgsModel = $factory();
        if ($actionArgsModel->loadByActionId($actionId)) {
            $actionArgsModel->delete();
        };
    }

    public function onActionSchedulerDisableAction($actionId)
    {
        $factory = $this->actionArgsModelFactory;
        $actionArgsModel = $factory();
        if ($actionArgsModel->loadByActionId($actionId)) {
            $actionArgsModel->setEnabled(false);
            $actionArgsModel->save();
        };
    }

    /**
     * @return \PublishPress\Future\Modules\Expirator\Tables\ScheduledActionsTable
     */
    private function getListTable()
    {
        if (null === $this->listTable) {
            $factory = $this->scheduledActionsTableFactory;
            $this->listTable = $factory();
            $this->listTable->process_actions();
        }

        return $this->listTable;
    }

    public function processAdminUi()
    {
        $this->getListTable();
    }

    public function filterScreenSettings($screenSettings, $screen)
    {
        if ($screen->id !== 'future_page_publishpress-future-scheduled-actions') {
            return $screenSettings;
        }

        $userLogFormat = get_user_meta(get_current_user_id(), 'publishpressfuture_actions_log_format', true);
        if (empty($userLogFormat)) {
            $userLogFormat = 'list';
        }

        // Add nonce field
        $screenSettings .= wp_nonce_field(
            'publishpressfuture_actions_log_format',
            'publishpressfuture_actions_log_format_nonce',
            true,
            false
        );

        $screenSettings .= '<fieldset class="metabox-prefs">';
        $screenSettings .= '<legend>' . esc_html__('Log format', 'post-expirator') . '</legend>';
        $screenSettings .= '<label for="' . $screen->id . '_log_format_list">';
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $screenSettings .= '<input type="radio" id="' . $screen->id . '_log_format_list" name="publishpressfuture_actions_log_format" value="list" ' . checked(
            $userLogFormat,
            'list',
            false
        ) . '>';
        $screenSettings .= esc_html__('List', 'post-expirator');
        $screenSettings .= '</label>';
        $screenSettings .= '&nbsp;';
        $screenSettings .= '<label for="' . $screen->id . '_log_format_popup">';
        // phpcs:ignore Generic.Files.LineLength.TooLong
        $screenSettings .= '<input type="radio" id="' . $screen->id . '_log_format_popup" name="publishpressfuture_actions_log_format" value="popup" ' . checked(
            $userLogFormat,
            'popup',
            false
        ) . '>';
        $screenSettings .= esc_html__('Popup', 'post-expirator');
        $screenSettings .= '</label>';

        $screenSettings .= '</fieldset>';

        return $screenSettings;
    }

    public function filterSetScreenOption($status)
    {
        if (
            ! isset($_POST['publishpressfuture_actions_log_format_nonce']) || ! wp_verify_nonce(
                sanitize_key($_POST['publishpressfuture_actions_log_format_nonce']),
                'publishpressfuture_actions_log_format'
            )
        ) {
            return $status;
        }

        if (isset($_POST['publishpressfuture_actions_log_format'])) {
            update_user_meta(
                get_current_user_id(),
                'publishpressfuture_actions_log_format',
                sanitize_key($_POST['publishpressfuture_actions_log_format'])
            );
        } else {
            delete_user_meta(get_current_user_id(), 'publishpressfuture_actions_log_format');
        }

        return $status;
    }

    public function enqueueScripts($screenId)
    {
        try {
            if ('admin_page_publishpress-future-scheduled-actions' === $screenId) {
                wp_enqueue_style(
                    'postexpirator-css',
                    Plugin::getAssetUrl('css/style.css'),
                    false,
                    PUBLISHPRESS_FUTURE_VERSION
                );

                wp_enqueue_style(
                    'pe-footer',
                    Plugin::getAssetUrl('css/footer.css'),
                    false,
                    PUBLISHPRESS_FUTURE_VERSION
                );
            }
        } catch (Throwable $th) {
            $this->logger->error('Error enqueuing scripts: ' . $th->getMessage());
        }
    }

    public function filterActionSchedulerAdminNotice($html, $action, $notification)
    {
        if ($action->get_group() !== 'publishpress-future') {
            return $html;
        }

        $hook = $action->get_hook();

        if ($hook === HooksAbstract::ACTION_RUN_WORKFLOW) {
            $args = $action->get_args();
            if (isset($args['postId']) && isset($args['workflow']) && 'expire' === $args['workflow']) {
                $postId = (int)$args['postId'];
                $post = get_post($postId);
                $postTitle = $post ? html_entity_decode(get_the_title($post)) : __('Unknown post', 'post-expirator');

                $html = sprintf(
                    __('Executed action for: %s (ID: %d)', 'post-expirator'),
                    $postTitle,
                    $postId
                );
            } else {
                $html = __('Executed scheduled action', 'post-expirator');
            }
        }

        return $html;
    }

    public function onFilterAdminTitle($adminTitle, $title)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No need for nonce verification when just comparing page
        if (isset($_GET['page']) && $_GET['page'] === 'publishpress-future-scheduled-actions') {
            return str_replace($title, __('Scheduled Actions', 'post-expirator'), $adminTitle);
        }

        return $adminTitle;
    }
}
