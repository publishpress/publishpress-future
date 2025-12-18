<?php

namespace PublishPress\Future\Modules\Workflows\Controllers;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Core\HooksAbstract as CoreHooksAbstract;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\StepTypesModelInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;
use PublishPress\Future\Modules\Workflows\Models\ScheduledActionsModel;
use PublishPress\Future\Modules\Workflows\CapabilitiesAbstract as WorkflowCapabilities;
use PublishPress\Future\Modules\Workflows\Module;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use Throwable;

class WorkflowsList implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var StepTypesModelInterface
     */
    private $stepTypesModel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    public function __construct(
        HookableInterface $hooks,
        StepTypesModelInterface $stepTypesModel,
        LoggerInterface $logger,
        SettingsFacade $settingsFacade
    ) {
        $this->hooks = $hooks;
        $this->stepTypesModel = $stepTypesModel;
        $this->logger = $logger;
        $this->settingsFacade = $settingsFacade;
    }

    public function initialize()
    {
        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_ADMIN_MENU,
            [$this, "adminMenu"],
            20
        );

        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_ADMIN_INIT,
            [$this, "fixWorkflowEditorPageTitle"]
        );

        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_ADMIN_ENQUEUE_SCRIPTS,
            [$this, "enqueueScriptsList"]
        );

        $this->hooks->addAction(
            "manage_" . Module::POST_TYPE_WORKFLOW . "_posts_columns",
            [$this, "addCustomColumns"]
        );

        $this->hooks->addFilter(
            HooksAbstract::FILTER_POST_ROW_ACTIONS,
            [$this, "renderWorkflowAction"],
            10,
            2
        );

        $this->hooks->addAction(
            "manage_" . Module::POST_TYPE_WORKFLOW . "_posts_custom_column",
            [$this, "renderTriggersColumn"],
            10,
            2
        );

        $this->hooks->addAction(
            "manage_" . Module::POST_TYPE_WORKFLOW . "_posts_custom_column",
            [$this, "renderStatusColumn"],
            10,
            2
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_ADMIN_POST_CHANGE_WORKFLOW_STATUS,
            [$this, "updateWorkflowStatus"]
        );

        $this->hooks->addFilter(
            HooksAbstract::FILTER_THE_TITLE,
            [$this, "addWorkflowStatusToTitle"],
            10,
            2
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_ADMIN_FOOTER,
            [$this, "addScheduledActionsButton"]
        );

        $this->hooks->addFilter(
            HooksAbstract::FILTER_POST_UPDATED_MESSAGES,
            [$this, "filterPostUpdatedMessages"]
        );

        $this->hooks->addFilter(
            HooksAbstract::FILTER_BULK_POST_UPDATED_MESSAGES,
            [$this, "filterBulkPostUpdatedMessages"],
            10,
            2
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_ADMIN_POST_COPY_WORKFLOW,
            [$this, "copyWorkflow"]
        );

        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_ADMIN_NOTICES,
            [$this, 'showWorkflowsNotice']
        );

        $this->hooks->addFilter(
            CoreHooksAbstract::FILTER_REMOVABLE_QUERY_ARGS,
            [$this, 'addRemovableQueryArgs']
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_ADMIN_POST_CANCEL_SCHEDULED_ACTIONS,
            [$this, "handleCancelScheduledActions"]
        );
    }

    public function adminMenu()
    {
        try {
            global $submenu;

            if (!isset($submenu["publishpress-future"])) {
                return;
            }

            $this->renameWorkflowsSubmenu();

            add_submenu_page(
                '',
                "Action Workflow Editor",
                "Action Workflow Editor",
                WorkflowCapabilities::EDIT_WORKFLOWS,
                "future_workflow_editor",
                [$this, "renderEditorPage"]
            );
        } catch (Throwable $th) {
            $this->logger->error('Error adding workflows menu: ' . $th->getMessage());
        }
    }

    private function renameWorkflowsSubmenu()
    {
        global $submenu;

        $indexAllWorkflows = array_search(
            "edit.php?post_type=" . Module::POST_TYPE_WORKFLOW,
            array_column($submenu["publishpress-future"], 2)
        );

        $submenu["publishpress-future"][$indexAllWorkflows][0] = __("Action Workflows", "post-expirator");
    }

    public function renderEditorPage()
    {
        $this->hooks->doAction(HooksAbstract::ACTION_RENDER_WORKFLOW_EDITOR_PAGE);
    }

    public function enqueueScriptsList($hook)
    {
        if ("edit.php" !== $hook) {
            return;
        }

        global $post_type;
        if (Module::POST_TYPE_WORKFLOW !== $post_type) {
            return;
        }

        wp_enqueue_style(
            'pp-future-workflows-list',
            Plugin::getAssetUrl('css/workflows-list.css'),
            [],
            PUBLISHPRESS_FUTURE_VERSION
        );

        wp_enqueue_script(
            'pp-future-workflows-list-cancel-actions',
            Plugin::getAssetUrl('js/workflowsListCancelAction.js'),
            ['wp-element', 'wp-components', 'wp-i18n'],
            PUBLISHPRESS_FUTURE_VERSION,
            true
        );

        wp_enqueue_script(
            'pp-future-workflows-list-status-change',
            Plugin::getAssetUrl('js/workflowsListStatusChange.js'),
            ['wp-element', 'wp-components', 'wp-i18n'],
            PUBLISHPRESS_FUTURE_VERSION,
            true
        );

        wp_enqueue_script(
            'pp-future-workflows-list-copy-workflow',
            Plugin::getAssetUrl('js/workflowsListCopyWorkflow.js'),
            ['wp-element', 'wp-components', 'wp-i18n'],
            PUBLISHPRESS_FUTURE_VERSION,
            true
        );
    }

    public function addCustomColumns($columns)
    {
        $columns["workflow_status"] = __("Status", "post-expirator");

        $columns["workflow_triggers"] = __(
            "Triggers",
            "post-expirator"
        );

        // Move the date column to the end
        if (isset($columns["date"])) {
            $date = $columns["date"];
            unset($columns["date"]);
            $columns["date"] = $date;
        }

        return $columns;
    }

    public function renderStatusColumn($column, $postId)
    {
        if ("workflow_status" !== $column) {
            return;
        }

        $workflowModel = new WorkflowModel();
        $workflowModel->load($postId);

        $workflowStatus = $workflowModel->getStatus();
        $isActive = $workflowStatus === 'publish';

        $this->renderStatusToggleForm($postId, $isActive);
    }

    public function renderTriggersColumn($column, $postId)
    {
        if ("workflow_triggers" !== $column) {
            return;
        }

        $workflowModel = new WorkflowModel();
        $workflowModel->load($postId);

        $workflowFlow = $workflowModel->getFlow();

        $triggers = [];

        if (empty($workflowFlow) || !isset($workflowFlow["nodes"])) {
            esc_html_e("—", "post-expirator");
            return;
        }

        foreach ($workflowFlow["nodes"] as $node) {
            if (
                StepTypesModel::STEP_TYPE_TRIGGER ===
                $node["data"]["elementaryType"]
            ) {
                $nodeType = $this->stepTypesModel->getStepType($node["data"]["name"]);

                if (empty($nodeType)) {
                    $triggers[] = esc_html($node["data"]["name"]);
                } else {
                    $triggers[] = $nodeType->getLabel();
                }
            }
        }

        echo esc_html(implode(", ", $triggers));
    }

    public function updateWorkflowStatus()
    {
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['workflow_id'])) {
            return;
        }

        if (!isset($_POST['new_status'])) {
            return;
        }

        if (!isset($_POST['workflow_nonce'])) {
            return;
        }

        if (!current_user_can(WorkflowCapabilities::EDIT_WORKFLOWS)) {
            return;
        }

        if (!wp_verify_nonce(sanitize_key($_POST['workflow_nonce']), 'workflow_status_change')) {
            return;
        }

        $workflowId = (int) $_POST['workflow_id'];
        $newStatus = sanitize_key($_POST['new_status']);

        try {
            $workflowModel = new WorkflowModel();
            $workflowModel->load($workflowId);

            if ('publish' === $newStatus) {
                $workflowModel->publish();
            } else {
                $workflowModel->unpublish();
            }


            wp_safe_redirect(
                add_query_arg(
                    [
                        'post_type' => Module::POST_TYPE_WORKFLOW,
                        'pp_workflow_notice' => 'status_changed',
                        'pp_workflow_notice_type' => 'success'
                    ],
                    admin_url('edit.php')
                )
            );
            exit;
        } catch (Throwable $th) {
            $this->logger->error('Error updating workflow status: ' . $th->getMessage());

            wp_safe_redirect(
                add_query_arg(
                    [
                        'post_type' => Module::POST_TYPE_WORKFLOW,
                        'pp_workflow_notice' => 'status_change_error',
                        'pp_workflow_notice_type' => 'error'
                    ],
                    admin_url('edit.php')
                )
            );
            exit;
        }
    }

    public function renderWorkflowAction($actions, $post)
    {
        if (Module::POST_TYPE_WORKFLOW !== $post->post_type) {
            return $actions;
        }

        if (!current_user_can(WorkflowCapabilities::EDIT_WORKFLOWS)) {
            return;
        }

        $workflowModel = new WorkflowModel();
        $workflowModel->load($post->ID);

        $workflowStatus = $workflowModel->getStatus();

        $newActions = $this->getWorkflowActionForms($post, $workflowStatus);

        $quickEditIndex = array_search('inline hide-if-no-js', array_keys($actions));

        if ($quickEditIndex !== false) {
            $insertPosition = $quickEditIndex + 1;
            $actions = array_merge(
                array_slice($actions, 0, $insertPosition),
                $newActions,
                array_slice($actions, $insertPosition)
            );
        } else {
            $actions = array_merge($newActions, $actions);
        }

        return $actions;
    }

    public function copyWorkflow()
    {
        $postType = Module::POST_TYPE_WORKFLOW;

        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['workflow_id']) || !isset($_POST['workflow_nonce'])) {
            return;
        }

        if (!wp_verify_nonce(sanitize_key($_POST['workflow_nonce']), 'workflow_copy')) {
            return;
        }

        if (!current_user_can(WorkflowCapabilities::EDIT_WORKFLOWS)) {
            return;
        }

        $sourceWorkflowId = (int) $_POST['workflow_id'];
        $redirect_url = admin_url('edit.php?post_type=' . $postType);

        try {
            // load source workflow
            $sourceWorkflowModel = new WorkflowModel();
            if (!$sourceWorkflowModel->load($sourceWorkflowId)) {
                $redirect_url = add_query_arg(
                    [
                        'pp_workflow_notice' => 'source_not_found',
                        'pp_workflow_notice_type' => 'error'
                    ],
                    $redirect_url
                );
                wp_safe_redirect(
                    esc_url_raw($redirect_url)
                );
                exit;
            }

            // clone source workflow
            $newWorkflowModel = $sourceWorkflowModel->createCopy();
            if (!$newWorkflowModel || !is_object($newWorkflowModel)) {
                $redirect_url = add_query_arg(
                    [
                        'pp_workflow_notice' => 'create_failed',
                        'pp_workflow_notice_type' => 'error'
                    ],
                    $redirect_url
                );
                wp_safe_redirect(esc_url_raw($redirect_url));
                exit;
            }

            // Redirect to the workflows list on succesfull clone
            $redirect_url = add_query_arg(
                [
                    'pp_workflow_notice' => 'copy_success',
                    'pp_workflow_notice_type' => 'success'
                ],
                $redirect_url
            );
            wp_safe_redirect(
                esc_url_raw($redirect_url)
            );
            exit;
        } catch (Throwable $th) {
            $this->logger->error('Error copying workflow: ' . $th->getMessage());

            $redirect_url = add_query_arg(
                [
                    'pp_workflow_notice' => 'generic_error',
                    'pp_workflow_notice_type' => 'error'
                ],
                $redirect_url
            );
            wp_safe_redirect(
                esc_url_raw($redirect_url)
            );
            exit;
        }
    }

    public function handleCancelScheduledActions()
    {
        $postType = Module::POST_TYPE_WORKFLOW;

        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!isset($_POST['workflow_id'])) {
            return;
        }

        if (!isset($_POST['workflow_nonce'])) {
            return;
        }

        if (!wp_verify_nonce(sanitize_key($_POST['workflow_nonce']), 'workflow_cancel_actions')) {
            return;
        }

        if (!current_user_can(WorkflowCapabilities::EDIT_WORKFLOWS)) {
            return;
        }

        $redirect_url = admin_url('edit.php?post_type=' . $postType);
        $workflowId = (int) $_POST['workflow_id'];

        try {
            $scheduledActionsModel = new ScheduledActionsModel();

            // Check if workflow has scheduled actions
            $hasScheduledActions = $scheduledActionsModel->workflowHasScheduledActions($workflowId);
            if (!$hasScheduledActions) {
                $redirect_url = add_query_arg(
                    [
                        'pp_workflow_notice' => 'scheduled_action_cancelling_empty',
                        'pp_workflow_notice_type' => 'error'
                    ],
                    $redirect_url
                );
                wp_safe_redirect(
                    esc_url_raw($redirect_url)
                );
                exit;
            }

            // Cancel scheduled actions
            $scheduledActionsModel->cancelWorkflowScheduledActions($workflowId);

            // Redirect back to the workflows list with a success message
            $redirect_url = add_query_arg(
                [
                    'pp_workflow_notice' => 'scheduled_action_cancelling_success',
                    'pp_workflow_notice_type' => 'success'
                ],
                $redirect_url
            );
            wp_safe_redirect(
                esc_url_raw($redirect_url)
            );
            exit;
        } catch (Throwable $th) {
            $this->logger->error('Error cancelling scheduled actions: ' . $th->getMessage());

            $redirect_url = add_query_arg(
                [
                    'pp_workflow_notice' => 'scheduled_action_cancelling_error',
                    'pp_workflow_notice_type' => 'error'
                ],
                $redirect_url
            );
            wp_safe_redirect(
                esc_url_raw($redirect_url)
            );
            exit;
        }
    }

    private function renderStatusToggleForm($postId, $isActive)
    {
        $title = $isActive ? __('Deactivate', 'post-expirator') : __('Activate', 'post-expirator');
        $newStatus = $isActive ? 'draft' : 'publish';
        $icon = $isActive ? 'yes' : 'no';
        $iconClass = $isActive ? 'active' : 'inactive';
        ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display: inline;">
            <?php wp_nonce_field('workflow_status_change', 'workflow_nonce'); ?>
            <input type="hidden" name="action" value="publishpress_future_change_workflow_status" />
            <input type="hidden" name="workflow_id" value="<?php echo esc_attr($postId); ?>" />
            <input type="hidden" name="new_status" value="<?php echo esc_attr($newStatus); ?>" />
            <button type="submit" class="button-link" style="border: none; background: none; padding: 0; cursor: pointer;">
                <i class="pp-future-workflow-status-icon dashicons dashicons-<?php echo esc_attr($icon); ?> <?php echo esc_attr($iconClass); ?>" title="<?php echo esc_attr($title); ?>"></i>
            </button>
        </form>
        <?php
    }

    private function getWorkflowActionForms($post, $workflowStatus): array
    {
        $statuses = [
            'draft' => [
                'action' => 'activate',
                'text' => __('Activate', 'post-expirator'),
                'title' => __('Activate', 'post-expirator'),
                'status' => 'publish',
            ],
            'publish' => [
                'action' => 'deactivate',
                'text' => __('Deactivate', 'post-expirator'),
                'title' => __('Deactivate', 'post-expirator'),
                'status' => 'draft',
            ]
        ];

        $statusData = isset($statuses[$workflowStatus]) ? $statuses[$workflowStatus] : [];

        if (empty($statusData)) {
            return [];
        }

        $actions = [];

        // Status change form
        $actions[$statusData['action']] = sprintf(
            '<form></form><!-- Dummy form to workaround WordPress stripping first form from row actions -->
            <form method="post" action="%s" class="pp-future-workflow-action-form">
                %s
                <input type="hidden" name="action" value="publishpress_future_change_workflow_status" />
                <input type="hidden" name="workflow_id" value="%s" />
                <input type="hidden" name="new_status" value="%s" />
                <button type="submit" class="button-link pp-future-workflow-status-change" data-action="%s" data-workflow-title="%s" title="%s">%s</button>
            </form>',
            esc_url(admin_url('admin-post.php')),
            wp_nonce_field('workflow_status_change', 'workflow_nonce', true, false),
            esc_attr($post->ID),
            esc_attr($statusData['status']),
            esc_attr($statusData['action']),
            esc_attr($post->post_title),
            esc_attr($statusData['title']),
            esc_html($statusData['text'])
        );

        // Cancel scheduled actions form
        $actions['cancel_scheduled_actions'] = sprintf(
            '<form method="post" action="%s" class="pp-future-workflow-action-form pp-cancel-form">
                %s
                <input type="hidden" name="action" value="publishpress_future_cancel_scheduled_actions" />
                <input type="hidden" name="workflow_id" value="%s" />
                <button type="submit" class="button-link pp-future-workflow-cancel-actions" data-workflow-title="%s" title="%s">%s</button>
            </form>',
            esc_url(admin_url('admin-post.php')),
            wp_nonce_field('workflow_cancel_actions', 'workflow_nonce', true, false),
            esc_attr($post->ID),
            esc_attr($post->post_title),
            esc_attr(__('Cancel all actions scheduled for this workflow', 'post-expirator')),
            esc_html(__('Cancel Scheduled Actions', 'post-expirator'))
        );

        // Copy workflow form
        $actions['copy'] = sprintf(
            '<form method="post" action="%s" class="pp-future-workflow-action-form">
                %s
                <input type="hidden" name="action" value="publishpress_future_copy_workflow" />
                <input type="hidden" name="workflow_id" value="%s" />
                <button type="submit" class="button-link pp-future-workflow-copy" data-workflow-title="%s" title="%s">%s</button>
            </form>',
            esc_url(admin_url('admin-post.php')),
            wp_nonce_field('workflow_copy', 'workflow_nonce', true, false),
            esc_attr($post->ID),
            esc_attr($post->post_title),
            esc_attr(__('Copy this workflow', 'post-expirator')),
            esc_html(__('Copy', 'post-expirator'))
        );

        return $actions;
    }

    public function addRemovableQueryArgs($args)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['post_type']) || $_GET['post_type'] !== Module::POST_TYPE_WORKFLOW) {
            return $args;
        }

        $args[] = 'pp_workflow_notice';
        $args[] = 'pp_workflow_notice_type';
        return $args;
    }

    public function showWorkflowsNotice()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['post_type']) || $_GET['post_type'] !== Module::POST_TYPE_WORKFLOW) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['pp_workflow_notice']) || !isset($_GET['pp_workflow_notice_type'])) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $notice = sanitize_key($_GET['pp_workflow_notice']);
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $type = sanitize_key($_GET['pp_workflow_notice_type']);

        $messages = [
            'error' => [
                // Copy workflow error
                'source_not_found'  => __('Source workflow not found.', 'post-expirator'),
                'create_failed'     => __('Failed to create new workflow.', 'post-expirator'),
                'generic_error'     => __('An error occurred while copying the workflow.', 'post-expirator'),
                // Cancel  scheduled workflow error
                'scheduled_action_cancelling_error' =>  __('Error cancelling scheduled actions.', 'post-expirator'),
                'scheduled_action_cancelling_empty' =>  __('This workflow doesn\'t have any scheduled action.', 'post-expirator'),
                // Worklow status change
                'status_change_error'   =>  __('Error updating workflow status.', 'post-expirator')
            ],
            'success' => [
                // Copy workflow success
                'copy_success'  => __('Workflow copied successfully.', 'post-expirator'),
                // Cancel scheduled workflow success
                'scheduled_action_cancelling_success' => __('Scheduled actions have been cancelled successfully.', 'post-expirator'),
                // Worklow status change
                'status_changed'   =>  __('Workflow status updated successfully.', 'post-expirator')
            ]
        ];

        if (isset($messages[$type][$notice])) {
            $class = $type === 'success' ? 'notice-success' : 'notice-error';
            echo sprintf(
                '<div class="notice %s is-dismissible"><p>%s</p></div>',
                esc_attr($class),
                esc_html($messages[$type][$notice])
            );
        }
    }

    public function addWorkflowStatusToTitle($title, $id = null)
    {
        if (!function_exists('get_current_screen') || empty($id)) {
            return $title;
        }

        $currentScreen = get_current_screen();

        if (
            !is_admin() || (
                // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                (!isset($_GET['post_type']) || $_GET['post_type'] !== Module::POST_TYPE_WORKFLOW) &&
                    ($currentScreen && $currentScreen->id !== 'edit-' . Module::POST_TYPE_WORKFLOW)
            )
        ) {
            return $title;
        }

        $workflowModel = new WorkflowModel();
        $workflowModel->load($id);

        if (empty($workflowModel)) {
            return $title;
        }

        return $title;
    }

    public function fixWorkflowEditorPageTitle()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['page']) || 'future_workflow_editor' !== $_GET['page']) {
            return;
        }

        global $title;

        $title = __("Action Workflow Editor", "post-expirator");
    }

    public function addScheduledActionsButton()
    {
        if (!is_admin()) {
            return;
        }

        global $current_screen;

        if (!isset($current_screen)) {
            return;
        }

        if (
            Module::POST_TYPE_WORKFLOW !== $current_screen->post_type
            && 'toplevel_page_publishpress-future' !== $current_screen->id
        ) {
            return;
        }

        $url = admin_url('admin.php?page=publishpress-future-scheduled-actions');

        $customButton = sprintf(
            '<a href="%s" class="page-title-action">%s</a>',
            esc_url($url),
            esc_html__('Scheduled Actions', 'post-expirator')
        );

        $titleClass = 'toplevel_page_publishpress-future' === $current_screen->id
            ? 'pp-settings-title'
            : 'page-title-action';

        // Insert the button into the DOM via JavaScript
        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        echo '<script type="text/javascript">
            jQuery(document).ready(function($) {
                $(".wrap .' . esc_js($titleClass) . ':first").after(\'' . $customButton . '\');
            });
        </script>';
        // phpcs:enable
    }

    /**
     * Customize the post messages for the Action Workflows
     *
     * @param array $messages
     *
     * @return array
     */
    public function filterPostUpdatedMessages($messages)
    {
        global $post, $current_screen;

        $postType = Module::POST_TYPE_WORKFLOW;

        if ($postType !== $current_screen->post_type) {
            return $messages;
        }

        $postTypeObject = get_post_type_object($postType);
        $singular       = $postTypeObject->labels->singular_name;
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Safe to use in post update message.
        $postRevision = isset($_GET['revision']) ? (int) $_GET['revision'] : null;

        $messages[$postType][1]  = sprintf(
            __('%s updated.', 'post-expirator'),
            $singular
        );
        $messages[$postType][4]  = sprintf(
            __('%s updated.', 'post-expirator'),
            $singular
        );
        $messages[$postType][5] = $postRevision
        ? sprintf(
            /* translators: 1: Post type singular label, 2: Revision title */
            __('%1$s restored to revision from %2$s', 'post-expirator'),
            $singular,
            wp_post_revision_title($postRevision, false)
        )
        : false;
        $messages[$postType][6]  = sprintf(
            __('%s published.', 'post-expirator'),
            $singular
        );
        $messages[$postType][7]  = sprintf(
            __('%s saved.', 'post-expirator'),
            $singular
        );
        $messages[$postType][8]  = sprintf(
            __('%s submitted.', 'post-expirator'),
            $singular
        );
        $messages[$postType][9] = sprintf(
            /* translators: 1: Post type singular label, 2: Scheduled date */
            __('%1$s scheduled for: <strong>%2$s</strong>.', 'post-expirator'),
            $singular,
            date_i18n('M j, Y @ G:i', strtotime($post->post_date))
        );
        $messages[$postType][10] = sprintf(
            __('%s draft updated.', 'post-expirator'),
            $singular
        );

        return $messages;
    }

    /**
     * Customize the post messages for the Action Workflows bulk action.
     *
     * @param array $bulk_messages
     * @param array $bulk_counts
     *
     * @return array
     */
    public function filterBulkPostUpdatedMessages($bulk_messages, $bulk_counts)
    {
        global $current_screen;

        $postType = Module::POST_TYPE_WORKFLOW;

        if ($postType !== $current_screen->post_type) {
            return $bulk_messages;
        }

        $postTypeObject     = get_post_type_object($postType);
        $singular           = $postTypeObject->labels->singular_name;
        $plural             = $postTypeObject->labels->name;

        $bulk_messages[$postType]['updated']   = _n(
            "%s $singular updated.",
            "%s $plural updated.",
            $bulk_counts['updated'],
            'post-expirator'
        );
        $bulk_messages[$postType]['locked']    = _n(
            "%s $singular not updated, someone is editing it.",
            "%s $plural not updated, someone is editing them.",
            $bulk_counts['locked'],
            'post-expirator'
        );
        $bulk_messages[$postType]['deleted']   = _n(
            "%s $singular permanently deleted.",
            "%s $plural permanently deleted.",
            $bulk_counts['deleted'],
            'post-expirator'
        );
        $bulk_messages[$postType]['trashed']   = _n(
            "%s $singular moved to the Trash.",
            "%s $plural moved to the Trash.",
            $bulk_counts['trashed'],
            'post-expirator'
        );
        $bulk_messages[$postType]['untrashed'] = _n(
            "%s $singular restored from the Trash.",
            "%s $plural restored from the Trash.",
            $bulk_counts['untrashed'],
            'post-expirator'
        );

        return $bulk_messages;
    }
}
