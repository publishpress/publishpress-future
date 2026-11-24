<?php

/**
 * Per-post workflow selection and explicitly requested native manual execution.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp\Services;

use Closure;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\WordPress\Facade\McpFacade;
use PublishPress\Future\Modules\Expirator\CapabilitiesAbstract as ExpiratorCapabilities;
use PublishPress\Future\Modules\Mcp\Registry;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Module as WorkflowModule;

\defined('ABSPATH') || exit;

class PostWorkflowsService
{
    private McpFacade $wordpress;
    private Closure $factory;
    private HookableInterface $hooks;

    /** @since 4.12.0 */
    public function __construct(McpFacade $wordpress, Closure $factory, HookableInterface $hooks)
    {
        $this->wordpress = $wordpress;
        $this->factory = $factory;
        $this->hooks = $hooks;
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     */
    public function canEdit(array $input): bool
    {
        $post = $this->wordpress->post($input['post_id'] ?? 0);
        return $post && $post->post_type !== WorkflowModule::POST_TYPE_WORKFLOW && $this->wordpress->can('edit_post', $post->ID)
            && $this->wordpress->can(ExpiratorCapabilities::EXPIRE_POST);
    }

    /** @since 4.12.0 */
    public function register(Registry $registry): void
    {
        $output = ['post_id' => ['type' => 'integer'], 'workflow_ids' => ['type' => 'array', 'items' => Registry::id()],
            'choices' => ['type' => 'array', 'items' => ['type' => 'object']], 'hash' => ['type' => 'string'], 'dry_run' => ['type' => 'boolean']];
        $registry->add(
            'get-post-workflows',
            __('Read post workflow choices', 'post-expirator'),
            __('Read the saved manual workflow selection and eligible enabled workflows for one editable post. Uses the native trigger post-query validator. Requires edit_post and Future scheduling permission; workflow choices also require read_post on the workflow.', 'post-expirator'),
            Registry::object(['post_id' => Registry::id()], ['post_id']),
            $output,
            [$this, 'get'],
            [$this, 'canEdit']
        );
        $registry->add(
            'update-post-workflows',
            __('Select workflows for a post', 'post-expirator'),
            __('Replace this post\'s selected workflow IDs using its latest hash and native eligible choices. Removing selections cancels their pending steps, as in the editor. Does not execute new selections; run-post-workflow starts a selected workflow explicitly. dry_run validates without saving or cancelling. Runtime cancellation cannot be rolled back.', 'post-expirator'),
            Registry::object(['post_id' => Registry::id(), 'workflow_ids' => ['type' => 'array', 'items' => Registry::id(), 'maxItems' => 100, 'uniqueItems' => true],
                'expected_hash' => Registry::revision(), 'dry_run' => Registry::preview()], ['post_id', 'workflow_ids', 'expected_hash']),
            $output,
            [$this, 'update'],
            [$this, 'canEdit'],
            true
        );
        $registry->add(
            'run-post-workflow',
            __('Run selected post workflow', 'post-expirator'),
            __('Explicitly dispatch one selected, enabled and eligible workflow through its native manual trigger. Requires the latest post-workflow hash. Can change content, send messages or schedule steps; execution is not reversible or idempotent. Dispatch does not guarantee all downstream steps completed. dry_run checks eligibility without triggering anything.', 'post-expirator'),
            Registry::object(
                ['post_id' => Registry::id(), 'workflow_id' => Registry::id(), 'expected_hash' => Registry::revision(), 'dry_run' => Registry::preview()],
                ['post_id', 'workflow_id', 'expected_hash']
            ),
            ['post_id' => ['type' => 'integer'], 'workflow_id' => ['type' => 'integer'], 'dispatched' => ['type' => 'boolean'], 'dry_run' => ['type' => 'boolean']],
            [$this, 'run'],
            [$this, 'canEdit'],
            true,
            false
        );
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return array<array-key, mixed>
     */
    public function get(array $input): array
    {
        $model = ($this->factory)();
        $model->load($input['post_id']);
        $selected = array_values($model->getManuallyEnabledWorkflows());
        $choices = [];
        foreach ($model->getValidWorkflowsWithManualTrigger($input['post_id']) as $choice) {
            if ($this->wordpress->can('read_post', (int) $choice['workflowId'])) {
                $choices[] = $choice;
            }
        }
        $revisions = [];
        foreach ($choices as $choice) {
            $id = (int) $choice['workflowId'];
            $revisions[$id] = [$this->wordpress->post($id), $this->wordpress->allMeta($id)];
        }
        return ['post_id' => $input['post_id'], 'workflow_ids' => $selected, 'choices' => $choices,
            'hash' => $this->wordpress->hash([$input['post_id'], $selected, $choices, $revisions,
                $this->wordpress->post($input['post_id']), $this->wordpress->allMeta($input['post_id'])]), 'dry_run' => false];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function update(array $input)
    {
        $before = $this->get($input);
        if ($before['hash'] !== $input['expected_hash']) {
            return Registry::error(__('Post workflows changed. Read them again before saving.', 'post-expirator'), 409);
        }
        $allowed = array_map('intval', array_column($before['choices'], 'workflowId'));
        if (array_diff($input['workflow_ids'], $allowed)) {
            return Registry::error(__('Choose enabled workflows eligible for this post.', 'post-expirator'));
        }
        if (!empty($input['dry_run'])) {
            return array_merge($before, ['workflow_ids' => $input['workflow_ids'], 'dry_run' => true]);
        }
        try {
            $model = ($this->factory)();
            $model->load($input['post_id']);
            $model->setManuallyEnabledWorkflows($input['workflow_ids']);
            $saved = $this->get($input);
            if ($saved['workflow_ids'] !== $input['workflow_ids']) {
                throw new \RuntimeException('Verification failed');
            }
            return $saved;
        } catch (\Throwable $error) {
            return Registry::error(__('Workflow selection could not be fully saved. Read it before retrying; pending steps may already have been cancelled.', 'post-expirator'), 500);
        }
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function run(array $input)
    {
        $before = $this->get($input);
        if ($before['hash'] !== $input['expected_hash']) {
            return Registry::error(__('Post workflows changed. Read them again before running.', 'post-expirator'), 409);
        }
        if (
            !in_array($input['workflow_id'], $before['workflow_ids'], true)
            || !in_array($input['workflow_id'], array_map('intval', array_column($before['choices'], 'workflowId')), true)
        ) {
            return Registry::error(__('Choose a selected and eligible workflow.', 'post-expirator'), 403);
        }
        if (empty($input['dry_run'])) {
            try {
                $this->hooks->doAction(HooksAbstract::ACTION_MANUALLY_TRIGGERED_WORKFLOW, $input['post_id'], $input['workflow_id']);
            } catch (\Throwable $error) {
                return Registry::error(__('Workflow execution did not finish normally. Inspect its scheduled actions before retrying; some steps may have run.', 'post-expirator'), 500);
            }
        }
        return ['post_id' => $input['post_id'], 'workflow_id' => $input['workflow_id'], 'dispatched' => empty($input['dry_run']), 'dry_run' => !empty($input['dry_run'])];
    }
}
