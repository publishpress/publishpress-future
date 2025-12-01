<?php

namespace PublishPress\Future\Modules\Workflows\Controllers;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Core\HooksAbstract as CoreHooksAbstract;
use PublishPress\Future\Modules\Workflows\Models\WorkflowsModel;
use PublishPress\Future\Core\HooksAbstract as FutureCoreHooksAbstract;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Facade\RequestFacade;
use PublishPress\Future\Framework\WordPress\Facade\SanitizationFacade;
use PublishPress\Future\Modules\Expirator\Models\CurrentUserModel;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Models\PostModel;
use PublishPress\Future\Modules\Workflows\Models\PostTypesModel;
use PublishPress\Future\Modules\Workflows\Module;
use Throwable;

class ManualPostTrigger implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var boolean
     */
    private $isBlockEditor = false;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SanitizationFacade
     */
    private $sanitization;

    /**
     * @var RequestFacade
     */
    private $request;

    /**
     * @var CurrentUserModel
     */
    private $currentUserModel;

    public function __construct(
        HookableInterface $hooks,
        LoggerInterface $logger,
        SanitizationFacade $sanitization,
        RequestFacade $request,
        CurrentUserModel $currentUserModel
    ) {
        $this->hooks = $hooks;
        $this->logger = $logger;
        $this->sanitization = $sanitization;
        $this->request = $request;
        $this->currentUserModel = $currentUserModel;
    }

    public function initialize()
    {
        // Quick Edit
        $this->hooks->addAction(
            FutureCoreHooksAbstract::ACTION_QUICK_EDIT_CUSTOM_BOX,
            [$this, 'registerQuickEditCustomBox'],
            10,
            2
        );

        $this->hooks->addAction(
            FutureCoreHooksAbstract::ACTION_SAVE_POST,
            [$this, 'processQuickEditUpdate']
        );

        $this->hooks->addAction(
            FutureCoreHooksAbstract::ACTION_ADMIN_INIT,
            [$this, 'processBulkEditUpdate']
        );

        $this->hooks->addAction(
            FutureCoreHooksAbstract::ACTION_ADMIN_PRINT_SCRIPTS_EDIT,
            [$this, 'enqueueQuickEditScripts']
        );

        // Bulk Edit
        $this->hooks->addAction(
            FutureCoreHooksAbstract::ACTION_ADMIN_PRINT_SCRIPTS_EDIT,
            [$this, 'enqueueBulkEditScripts']
        );

        // Block Editor
        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_ENQUEUE_BLOCK_EDITOR_ASSETS,
            [$this, 'enqueueBlockEditorScripts']
        );

        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_REST_API_INIT,
            [$this, 'registerRestField']
        );

        // Classic Editor
        $this->hooks->addAction(
            FutureCoreHooksAbstract::ACTION_ADD_META_BOXES,
            [$this, 'registerClassicEditorMetabox'],
            10,
            2
        );

        $this->hooks->addAction(
            FutureCoreHooksAbstract::ACTION_SAVE_POST,
            [$this, 'processMetaboxUpdate']
        );

        $this->hooks->addAction(
            FutureCoreHooksAbstract::ACTION_ADMIN_ENQUEUE_SCRIPTS,
            [$this, 'enqueueScripts']
        );
    }

    public function registerQuickEditCustomBox($columnName, $postType)
    {
        try {
            if ($columnName !== 'expirationdate' || Module::POST_TYPE_WORKFLOW === $postType) {
                return;
            }

            // Check there are workflows with the manual post trigger
            $workflowsModel = new WorkflowsModel();
            $workflows = $workflowsModel->getPublishedWorkflowsWithManualTrigger($postType);

            if (empty($workflows)) {
                return;
            }

            require_once __DIR__ . "/../Views/manual-trigger-quick-edit.html.php";
        } catch (Throwable $th) {
            $this->logger->error('Error registering quick edit custom box: ' . $th->getMessage());
        }
    }

    public function processQuickEditUpdate($postId)
    {
        try {
            // phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.NonceVerification.Missing
            // Don't run if this is an auto save
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            $postType = get_post_type((int)$postId);
            $postTypeToIgnore = ['revision', Module::POST_TYPE_WORKFLOW];
            if (in_array($postType, $postTypeToIgnore)) {
                return;
            }

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $view = $_POST['future_workflow_view'] ?? '';

            if (empty($view) || $view !== 'quick-edit') {
                return;
            }

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $manuallyEnabledWorkflows = $_POST['future_workflow_manual_trigger'] ?? [];
            $manuallyEnabledWorkflows = array_map('intval', $manuallyEnabledWorkflows);

            $postModel = new PostModel();
            $postModel->load($postId);

            $currentlyEnabledWorkflows = $postModel->getManuallyEnabledWorkflows();
            $postModel->setManuallyEnabledWorkflows($manuallyEnabledWorkflows);

            $notEnabledWorkflows = array_diff($manuallyEnabledWorkflows, $currentlyEnabledWorkflows);

            if (! empty($notEnabledWorkflows)) {
                $this->triggerManuallyEnabledWorkflow($postId, $notEnabledWorkflows);
            }
            // phpcs:enable
        } catch (Throwable $th) {
            $this->logger->error('Error processing quick edit update: ' . $th->getMessage());
        }
    }

    private function triggerManuallyEnabledWorkflow($postId, $manuallyEnabledWorkflows)
    {
        // Trigger the action to trigger those workflows
        foreach ($manuallyEnabledWorkflows as $workflowId) {
            $this->hooks->doAction(HooksAbstract::ACTION_MANUALLY_TRIGGERED_WORKFLOW, (int)$postId, (int)$workflowId);
        }
    }

    public function enqueueQuickEditScripts()
    {
        try {
            // Only enqueue scripts if we are in the post list table

            if (get_current_screen()->base !== 'edit') {
                return;
            }

            wp_enqueue_style("wp-components");

            wp_enqueue_script("wp-components");
            wp_enqueue_script("wp-plugins");
            wp_enqueue_script("wp-element");
            wp_enqueue_script("wp-data");

            wp_enqueue_script(
                "future_workflow_manual_selection_script_quick_edit",
                Plugin::getScriptUrl('workflowManualSelectionQuickEdit'),
                [
                    "wp-plugins",
                    "wp-components",
                    "wp-element",
                    "wp-data",
                ],
                PUBLISHPRESS_FUTURE_VERSION,
                true
            );

            wp_localize_script(
                "future_workflow_manual_selection_script_quick_edit",
                "futureWorkflowManualSelection",
                [
                    "nonce" => wp_create_nonce("wp_rest"),
                    "apiUrl" => rest_url("publishpress-future/v1"),
                ]
            );
        } catch (Throwable $th) {
            $this->logger->error('Error enqueuing quick edit scripts: ' . $th->getMessage());
        }
    }

    public function enqueueBulkEditScripts()
    {
        try {
            // Only enqueue scripts if we are in the post list table

            if (get_current_screen()->base !== 'edit') {
                return;
            }

            wp_enqueue_style("wp-components");

            wp_enqueue_script("wp-components");
            wp_enqueue_script("wp-plugins");
            wp_enqueue_script("wp-element");
            wp_enqueue_script("wp-data");

            wp_enqueue_script(
                "future_workflow_manual_selection_script_bulk_edit",
                Plugin::getScriptUrl('workflowManualSelectionBulkEdit'),
                [
                    "wp-plugins",
                    "wp-components",
                    "wp-element",
                    "wp-data",
                ],
                PUBLISHPRESS_FUTURE_VERSION,
                true
            );

            wp_localize_script(
                "future_workflow_manual_selection_script_bulk_edit",
                "futureWorkflowManualSelection",
                [
                    "nonce" => wp_create_nonce("wp_rest"),
                    "apiUrl" => rest_url("publishpress-future/v1"),
                    "postType" => get_post_type(),
                ]
            );
        } catch (Throwable $th) {
            $this->logger->error('Error enqueuing bulk edit scripts: ' . $th->getMessage());
        }
    }

    public function enqueueBlockEditorScripts()
    {
        try {
            global $post;

            if (! $post || is_null($post->ID)) {
                $this->logger->error('Post is null or ID is not set, cannot enqueue block editor scripts.');
                return;
            }

            $this->isBlockEditor = true;

            $postModel = new PostModel();
            $postModel->load($post->ID);

            $workflowsWithManualTrigger = $postModel->getValidWorkflowsWithManualTrigger($post->ID);

            if (empty($workflowsWithManualTrigger)) {
                return;
            }

            wp_enqueue_style("wp-components");

            wp_enqueue_script("wp-components");
            wp_enqueue_script("wp-plugins");
            wp_enqueue_script("wp-element");
            wp_enqueue_script("wp-data");

            wp_enqueue_script(
                "future_workflow_manual_selection_script_block_editor",
                Plugin::getScriptUrl('workflowManualSelectionBlockEditor'),
                [
                    "wp-plugins",
                    "wp-components",
                    "wp-element",
                    "wp-data",
                ],
                PUBLISHPRESS_FUTURE_VERSION,
                true
            );

            wp_localize_script(
                "future_workflow_manual_selection_script_block_editor",
                "futureWorkflowManualSelection",
                [
                    "nonce" => wp_create_nonce("wp_rest"),
                    "apiUrl" => rest_url("publishpress-future/v1"),
                    "postId" => $post->ID,
                ]
            );
        } catch (Throwable $th) {
            $this->logger->error('Error enqueuing block editor scripts: ' . $th->getMessage());
        }
    }

    /**
     * Used by the block editor to read and update post attributes.
     *
     * @return void
     */
    public function registerRestField()
    {
        try {
            $postTypesModel = new PostTypesModel();
            $postTypes = $postTypesModel->getPostTypes();

            foreach ($postTypes as $postType) {
                register_rest_field(
                    $postType->name,
                    'publishpress_future_workflow_manual_trigger',
                    [
                        'get_callback' => function ($post) {
                            $post = get_post();

                            if (! $post || is_null($post->ID)) {
                                return [
                                    'enabledWorkflows' => []
                                ];
                            }

                            $postModel = new PostModel();
                            $postModel->load($post->ID);

                            $enabledWorkflows = $postModel->getManuallyEnabledWorkflows();

                            return [
                                'enabledWorkflows' => $enabledWorkflows,
                            ];
                        },
                        'update_callback' => function ($manualTriggerAttributes, $post) {
                            $postModel = new PostModel();
                            $postModel->load($post->ID);

                            $manuallyEnabledWorkflows = $manualTriggerAttributes['enabledWorkflows'] ?? [];
                            $manuallyEnabledWorkflows = array_map('intval', $manuallyEnabledWorkflows);

                            $currentlyEnabledWorkflows = $postModel->getManuallyEnabledWorkflows();
                            $postModel->setManuallyEnabledWorkflows($manuallyEnabledWorkflows);

                            $notEnabledWorkflows = array_diff($manuallyEnabledWorkflows, $currentlyEnabledWorkflows);

                            if (! empty($notEnabledWorkflows)) {
                                $this->triggerManuallyEnabledWorkflow($post->ID, $notEnabledWorkflows);
                            }

                            return true;
                        },
                        'schema' => [
                            'description' => __('Workflow Manual Trigger', 'post-expirator'),
                            'type' => 'object',
                        ]
                    ]
                );
            }
        } catch (Throwable $th) {
            $this->logger->error('Error registering rest field: ' . $th->getMessage());
        }
    }

    private function getPost($post)
    {
        // Some 3rd party plugins send the post as an object of a different class.
        // Try to fallback to the WP_Post class looking for the ID.
        if ((! is_a($post, 'WP_Post')) && is_object($post)) {
            $id = null;

            if (isset($post->ID)) {
                $id = $post->ID;
            } elseif (isset($post->post_id)) {
                $id = $post->post_id;
            } elseif (method_exists($post, 'get_id')) {
                $id = $post->get_id();
            } elseif (isset($post->id)) {
                $id = $post->id;
            }

            if (! is_null($id)) {
                $post = get_post($id);
            }
        }

        if (! is_a($post, 'WP_Post')) {
            return false;
        }

        return $post;
    }

    public function registerClassicEditorMetabox($postType, $post = null)
    {
        try {
            $post = $this->getPost($post);

            if (!is_object($post) || is_null($post->ID)) {
                throw new \Exception('Post is null or ID is not set, cannot load workflows.');
                return;
            }

            if ($this->isBlockEditor) {
                return;
            }

            $postModel = new PostModel();
            $postModel->load($post->ID);

            $workflows = $postModel->getValidWorkflowsWithManualTrigger($post->ID);

            if (empty($workflows)) {
                return;
            }

            add_meta_box(
                'future_workflow_manual_trigger',
                __('Action Workflows', 'post-expirator'),
                [$this, 'renderClassicEditorMetabox'],
                $postType,
                'side',
                'default',
                [$post]
            );
        } catch (Throwable $th) {
            $this->logger->error('Error registering classic editor metabox: ' . $th->getMessage());
        }
    }

    public function renderClassicEditorMetabox($post)
    {
        try {
            require_once __DIR__ . "/../Views/manual-trigger-classic-editor.html.php";
        } catch (Throwable $th) {
            $this->logger->error('Error rendering classic editor metabox: ' . $th->getMessage());
        }
    }

    public function processMetaboxUpdate($postId)
    {
        try {
            // phpcs:disable WordPress.Security.NonceVerification.Missing
            // Don't run if this is an auto save
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            $postType = get_post_type((int)$postId);
            $postTypeToIgnore = ['revision', Module::POST_TYPE_WORKFLOW];
            if (in_array($postType, $postTypeToIgnore)) {
                return;
            }

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $view = $_POST['future_workflow_view'] ?? '';

            if (empty($view) || $view !== 'classic-editor') {
                return;
            }

            check_ajax_referer('__future_action', '_future_action_nonce');

            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $manuallyEnabledWorkflows = $_POST['future_workflow_manual_trigger'] ?? [];
            $manuallyEnabledWorkflows = array_map('intval', $manuallyEnabledWorkflows);

            $postModel = new PostModel();
            $postModel->load($postId);

            $currentlyEnabledWorkflows = $postModel->getManuallyEnabledWorkflows();
            $postModel->setManuallyEnabledWorkflows($manuallyEnabledWorkflows);

            $notEnabledWorkflows = array_diff($manuallyEnabledWorkflows, $currentlyEnabledWorkflows);

            if (! empty($notEnabledWorkflows)) {
                $this->triggerManuallyEnabledWorkflow($postId, $notEnabledWorkflows);
            }
            // phpcs:enable
        } catch (Throwable $th) {
            $this->logger->error('Error processing metabox update: ' . $th->getMessage());
        }
    }

    public function enqueueScripts()
    {
        try {
            // Only enqueue scripts if we are in the post edit screen
            if (get_current_screen()->id !== 'post') {
                return;
            }

            wp_enqueue_style("wp-components");

            wp_enqueue_script("wp-components");
            wp_enqueue_script("wp-plugins");
            wp_enqueue_script("wp-element");
            wp_enqueue_script("wp-data");

            wp_enqueue_script(
                "future_workflow_manual_selection_script",
                Plugin::getScriptUrl('workflowManualSelectionClassicEditor'),
                [
                    "wp-plugins",
                    "wp-components",
                    "wp-element",
                    "wp-data",
                ],
                PUBLISHPRESS_FUTURE_VERSION,
                true
            );

            $post = get_post();

            wp_localize_script(
                "future_workflow_manual_selection_script",
                "futureWorkflowManualSelection",
                [
                    "nonce" => wp_create_nonce("wp_rest"),
                    "apiUrl" => rest_url("publishpress-future/v1"),
                    "postId" => $post->ID,
                ]
            );
        } catch (Throwable $th) {
            $this->logger->error('Error enqueuing scripts: ' . $th->getMessage());
        }
    }

    public function processBulkEditUpdate()
    {
        try {
            // phpcs:disable WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $doAction = isset($_GET['action']) ? $this->sanitization->sanitizeKey($_GET['action']) : '';

            if (
                ('edit' !== $doAction)
                || (! isset($_REQUEST['future_action_bulk_view']))
                || ($_REQUEST['future_action_bulk_view'] !== 'bulk-edit')
                || (! isset($_REQUEST['future_workflow_manual_trigger']))
                || (! isset($_REQUEST['future_workflow_manual_strategy']))
            ) {
                return;
            }

            if (! $this->currentUserModel->userCanExpirePosts()) {
                return;
            }

            $this->request->checkAdminReferer('bulk-posts');

            $this->saveBulkEditData();
            // phpcs:enable
        } catch (Throwable $th) {
            $this->logger->error('Error processing bulk edit update: ' . $th->getMessage());
        }
    }

    private function saveBulkEditData()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $strategy = sanitize_text_field($_REQUEST['future_workflow_manual_strategy'] ?? 'no-change');
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        $postIds = array_map('intval', (array)$_REQUEST['post']);
        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated
        $selectedWorkflows = array_map('intval', (array)$_REQUEST['future_workflow_manual_trigger']);

        if (empty($postIds) || $strategy === 'no-change') {
            return;
        }

        $postModel = new PostModel();

        foreach ($postIds as $postId) {
            $postId = (int)$postId;

            if (! $this->currentUserModel->userCanEditPost($postId)) {
                continue;
            }

            $loaded = $postModel->load($postId);

            if (! $loaded) {
                continue;
            }

            $postModel->setManuallyEnabledWorkflows($selectedWorkflows);
        }
        // phpcs:enable
    }
}
