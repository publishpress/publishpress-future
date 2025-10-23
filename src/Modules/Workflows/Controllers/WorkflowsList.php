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
            CoreHooksAbstract::ACTION_ADMIN_INIT,
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
            CoreHooksAbstract::ACTION_ADMIN_INIT,
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
            CoreHooksAbstract::ACTION_ADMIN_INIT,
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
                "manage_options",
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

        $title = $isActive ? __('Deactivate', 'post-expirator') : __('Activate', 'post-expirator');

        $icon = $isActive ? 'yes' : 'no';
        $iconClass = $isActive ? 'active' : 'inactive';

        $toggleUrl = esc_url(
            wp_nonce_url(
                add_query_arg(
                    [
                        'pp_action' => 'change_workflow_status',
                        'workflow_id' => $postId,
                        'status' => $isActive ? 'draft' : 'publish'
                    ],
                    admin_url('edit.php?post_type=' . Module::POST_TYPE_WORKFLOW)
                ),
                'change_workflow_status_' . $postId
            )
        );

        echo sprintf(
            '<a href="%s"><i class="pp-future-workflow-status-icon dashicons dashicons-%s %s" title="%s"></i> </a>',
            esc_url($toggleUrl),
            esc_attr($icon),
            esc_attr($iconClass),
            esc_attr($title)
        );
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
        try {
            if (!isset($_GET['pp_action']) || 'change_workflow_status' !== $_GET['pp_action']) {
                return;
            }

            if (!isset($_GET['workflow_id'])) {
                return;
            }

            if (!isset($_GET['status'])) {
                return;
            }

            if (!isset($_GET['_wpnonce'])) {
                return;
            }

            if (!wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'change_workflow_status_' . (int) $_GET['workflow_id'])) {
                return;
            }

            $workflowId = (int) $_GET['workflow_id'];
            $workflowStatus = sanitize_key($_GET['status']);

            $workflowModel = new WorkflowModel();
            $workflowModel->load($workflowId);

            if ('publish' === $workflowStatus) {
                $workflowModel->publish();
            } else {
                $workflowModel->unpublish();
            }

            wp_redirect(
                esc_url(
                    add_query_arg(
                        'post_type',
                        Module::POST_TYPE_WORKFLOW,
                        admin_url('edit.php')
                    )
                )
            );

            exit;
        } catch (Throwable $th) {
            $this->logger->error('Error updating workflow status: ' . $th->getMessage());
        }

        exit;
    }

    public function renderWorkflowAction($actions, $post)
    {
        if (Module::POST_TYPE_WORKFLOW !== $post->post_type) {
            return $actions;
        }

        $workflowModel = new WorkflowModel();
        $workflowModel->load($post->ID);

        $workflowStatus = $workflowModel->getStatus();

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
            return $actions;
        }

        // New Action for status
        $newActions = [
            $statusData['action'] => sprintf(
                '<a href="%s" class="pp-future-workflow-%s-inline" title="%s">%s</a>',
                esc_url(
                    wp_nonce_url(
                        add_query_arg(
                            [
                                'pp_action' => 'change_workflow_status',
                                'workflow_id' => $post->ID,
                                'status' => $statusData['status']
                            ],
                            admin_url('edit.php?post_type=' . Module::POST_TYPE_WORKFLOW)
                        ),
                        'change_workflow_status_' . $post->ID
                    )
                ),
                $statusData['action'],
                $statusData['text'],
                $statusData['title']
            )
        ];

        // add cancel scheduled actions
        $newActions['cancel_scheduled_actions'] = sprintf(
            '<a href="%s" class="pp-future-workflow-cancel-actions" title="%s" data-workflow-title="%s">%s</a>',
            esc_url(
                wp_nonce_url(
                    add_query_arg(
                        [
                            'pp_action' => 'cancel_workflow_scheduled_actions',
                            'workflow_id' => $post->ID
                        ],
                        admin_url('edit.php?post_type=' . Module::POST_TYPE_WORKFLOW)
                    ),
                    'cancel_workflow_scheduled_actions_' . $post->ID
                )
            ),
            __('Cancel all actions scheduled for this workflow', 'post-expirator'),
            esc_attr($post->post_title),
            __('Cancel Scheduled Actions', 'post-expirator'),
        );

        // add copy action
        $newActions['copy'] = sprintf(
            '<a href="%s" class="pp-future-workflow-copy-inline" title="%s">%s</a>',
            esc_url(
                wp_nonce_url(
                    add_query_arg(
                        [
                            'pp_action' => 'copy_workflow',
                            'workflow_id' => $post->ID
                        ],
                        admin_url('edit.php?post_type=' . Module::POST_TYPE_WORKFLOW)
                    ),
                    'copy_workflow_' . $post->ID
                )
            ),
            __('Copy this workflow', 'post-expirator'),
            __('Copy', 'post-expirator')
        );

        $actions = $newActions + $actions;

        return $actions;
    }

    public function copyWorkflow()
    {
        $postType = Module::POST_TYPE_WORKFLOW;

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['post_type']) || $_GET['post_type'] !== $postType) {
            return;
        }

        if (!isset($_GET['pp_action']) || 'copy_workflow' !== $_GET['pp_action']) {
            return;
        }

        if (!isset($_GET['workflow_id']) || !isset($_GET['_wpnonce'])) {
            return;
        }

        if (!wp_verify_nonce(sanitize_key($_GET['_wpnonce']), 'copy_workflow_' . (int) $_GET['workflow_id'])) {
            return;
        }

        $redirect_url = admin_url('edit.php?post_type=' . $postType);

        try {
            $sourceWorkflowId = (int) $_GET['workflow_id'];
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

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if (!isset($_GET['post_type']) || $_GET['post_type'] !== $postType) {
            return;
        }

        if (!isset($_GET['pp_action']) || 'cancel_workflow_scheduled_actions' !== $_GET['pp_action']) {
            return;
        }

        try {
            if (!isset($_GET['workflow_id'])) {
                return;
            }

            if (!isset($_GET['_wpnonce'])) {
                return;
            }

            if (
                !wp_verify_nonce(
                    sanitize_key($_GET['_wpnonce']),
                    'cancel_workflow_scheduled_actions_' . (int) $_GET['workflow_id']
                )
            ) {
                return;
            }

            $redirect_url = admin_url('edit.php?post_type=' . $postType);
            $workflowId = (int) $_GET['workflow_id'];

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
                'scheduled_action_cancelling_error'         =>  __('Error cancelling scheduled actions.', 'post-expirator'),
                'scheduled_action_cancelling_empty'         =>  __('This workflow doesn\'t have any scheduled action.', 'post-expirator')
            ],
            'success' => [
                // Copy workflow success
                'copy_success'  => __('Workflow copied successfully.', 'post-expirator'),
                // Cancel scheduled workflow success
                'scheduled_action_cancelling_success' => __('Scheduled actions have been cancelled successfully.', 'post-expirator')
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
