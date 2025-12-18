<?php

namespace PublishPress\Future\Modules\Workflows\Controllers;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorModuleHooksAbstract;
use PublishPress\Future\Core\HooksAbstract as CoreHooksAbstract;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\LegacyAction\TriggerWorkflow;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowsModel;
use Throwable;

class FutureLegacyAction implements InitializableInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(HookableInterface $hooks, LoggerInterface $logger)
    {
        $this->hooks = $hooks;
        $this->logger = $logger;
    }

    public function initialize()
    {
        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_ADMIN_ENQUEUE_SCRIPTS,
            [$this, "enqueueScriptsLegacyAction"]
        );

        $this->hooks->addFilter(
            ExpiratorModuleHooksAbstract::FILTER_PREPARE_POST_EXPIRATION_OPTS,
            [$this, "preparePostExpirationOpts"],
            10,
            2
        );
    }

    public function enqueueScriptsLegacyAction($hook)
    {
        try {
            wp_enqueue_style("wp-components");

            wp_enqueue_script("wp-components");
            wp_enqueue_script("wp-plugins");
            wp_enqueue_script("wp-element");
            wp_enqueue_script("wp-data");

            wp_enqueue_script(
                "future_workflow_legacy_action_script",
                Plugin::getScriptUrl('legacyAction'),
                [
                    "wp-plugins",
                    "wp-plugins",
                    "wp-components",
                    "wp-element",
                    "wp-data",
                ],
                PUBLISHPRESS_FUTURE_VERSION,
                true
            );

            $workflowsModel = new WorkflowsModel();
            $workflows = $workflowsModel->getPublishedWorkflowsWithLegacyTriggerAsOptions();

            wp_localize_script(
                "future_workflow_legacy_action_script",
                "futureWorkflows",
                [
                    "workflows" => $workflows,
                    "apiUrl" => rest_url("publishpress-future/v1"),
                    "nonce" => wp_create_nonce("wp_rest"),
                    "workflowNonce" => wp_create_nonce("pp_workflow_action"),
                ]
            );
        } catch (Throwable $th) {
            $this->logger->error('Error enqueuing scripts: ' . $th->getMessage());
        }
    }

    public function preparePostExpirationOpts($opts, $postId)
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $validViews = [
            'quick-edit',
            'bulk-edit',
            'classic-editor',
            'block-editor',
        ];

        if (isset($_REQUEST['future_action_bulk_view'])) {
            $_REQUEST['future_action_view'] = 'bulk-edit';
            $_REQUEST['future_action_action'] = sanitize_key($_REQUEST['future_action_bulk_action'] ?? '');
        }

        // Check if it is a REST call to the WP rest API
        if (defined('REST_REQUEST') && REST_REQUEST) {
            // Get the workflow ID from the $opts['extraData'] array
            if (isset($opts['extraData']['workflow'])) {
                $_REQUEST['future_action_view'] = 'block-editor';
                $_REQUEST['future_action_action'] = 'trigger-workflow';
                $_REQUEST['future_action_pro_workflow'] = (int) $opts['extraData']['workflow'];
            }
        }

        if (!isset($_REQUEST['future_action_view']) || !in_array($_REQUEST['future_action_view'], $validViews)) {
            return $opts;
        }

        if (
            !isset($_REQUEST['future_action_action'])
            || TriggerWorkflow::ACTION_NAME !== $_REQUEST['future_action_action']
        ) {
            return $opts;
        }

        $workflowId = (int)$_REQUEST['future_action_pro_workflow'] ?? 0;

        if (empty($workflowId)) {
            return $opts;
        }

        $opts['workflowId'] = $workflowId;
        $workflowModel = new WorkflowModel();
        $workflowModel->load($workflowId);

        $opts['workflowTitle'] = $workflowModel->getTitle();

        return $opts;
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
    }
}
