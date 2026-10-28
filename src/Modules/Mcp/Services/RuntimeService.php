<?php

/**
 * Inspect and operate only Future-owned scheduled actions.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp\Services;

use Closure;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Facade\McpFacade;
use PublishPress\Future\Modules\Expirator\Adapters\CronToWooActionSchedulerAdapter;
use PublishPress\Future\Modules\Expirator\CapabilitiesAbstract as ExpiratorCapabilities;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;
use PublishPress\Future\Modules\Mcp\Registry;
use PublishPress\Future\Modules\Workflows\CapabilitiesAbstract as WorkflowCapabilities;
use PublishPress\Future\Modules\Workflows\HooksAbstract as WorkflowHooks;

\defined('ABSPATH') || exit;

class RuntimeService
{
    private McpFacade $wordpress;
    private \ActionScheduler_Store $store;
    private \ActionScheduler_QueueRunner $runner;
    private LoggerInterface $logger;
    private Closure $stepFactory;
    private SchedulesService $schedules;

    /** @since 4.12.0 */
    public function __construct(McpFacade $wordpress, \ActionScheduler_Store $store, \ActionScheduler_QueueRunner $runner, LoggerInterface $logger, Closure $stepFactory, SchedulesService $schedules)
    {
        $this->wordpress = $wordpress;
        $this->store = $store;
        $this->runner = $runner;
        $this->logger = $logger;
        $this->stepFactory = $stepFactory;
        $this->schedules = $schedules;
    }

    /** @since 4.12.0 */
    public function canManage(): bool
    {
        return $this->wordpress->can('manage_options');
    }

    /** @since 4.12.0 */
    public function register(Registry $registry): void
    {
        $output = ['action' => ['type' => 'object'], 'hash' => ['type' => 'string'], 'dry_run' => ['type' => 'boolean']];
        $registry->add(
            'list-scheduled-actions',
            __('Inspect Future scheduled actions', 'post-expirator'),
            __('Read a bounded page of Future-owned simple actions and workflow steps by status. Returns IDs, UTC schedule, status and permitted related post/workflow IDs, without execution context or arbitrary Action Scheduler arguments. Requires manage_options plus related object permissions.', 'post-expirator'),
            Registry::object(array_merge(Registry::page(), ['status' => ['type' => 'string', 'enum' => ['pending', 'failed', 'complete', 'canceled', 'in-progress'], 'default' => 'pending']])),
            ['items' => ['type' => 'array', 'items' => ['type' => 'object']], 'page' => ['type' => 'integer'], 'total_pages' => ['type' => 'integer']],
            [$this, 'listing'],
            [$this, 'canManage']
        );
        $registry->add(
            'get-scheduled-action',
            __('Read Future scheduled action', 'post-expirator'),
            __('Read one verified Future action and current revision before cancelling or running it. Foreign scheduler groups and maintenance hooks are excluded.', 'post-expirator'),
            Registry::object(['id' => Registry::id()], ['id']),
            $output,
            [$this, 'get'],
            [$this, 'canManage']
        );
        foreach (['cancel' => __('Cancel Future scheduled action', 'post-expirator'), 'run' => __('Run pending Future action now', 'post-expirator')] as $operation => $label) {
            $registry->add(
                $operation . '-scheduled-action',
                $label,
                __('Operate on one verified pending Future action using its latest hash and native Action Scheduler service. Requires manage_options and related post/workflow action permissions. Running can change content, delete data or send messages; it cannot be rolled back. Failed actions are not blindly cloned or replayed. dry_run checks without executing or cancelling.', 'post-expirator'),
                Registry::object(['id' => Registry::id(), 'expected_hash' => Registry::revision(), 'dry_run' => Registry::preview()], ['id', 'expected_hash']),
                $output,
                [$this, $operation],
                [$this, 'canManage'],
                true,
                $operation === 'cancel'
            );
        }
        $registry->add(
            'get-debug-status',
            __('Read Future diagnostics', 'post-expirator'),
            __('Read debug enabled state and log counts from the native logger. Raw log bodies, credentials and execution contexts are not returned; inspect full logs in the native Debug screen. Requires manage_options.', 'post-expirator'),
            Registry::object(),
            ['enabled' => ['type' => 'boolean'], 'total_logs' => ['type' => 'integer'], 'size_bytes' => ['type' => 'integer'],
            'pro_active' => ['type' => 'boolean'],
            'timezone' => ['type' => 'string']],
            [$this, 'diagnostics'],
            [$this, 'canManage']
        );
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function diagnostics(): array
    {
        return ['enabled' => (bool) $this->logger->isDebugEnabled(), 'total_logs' => (int) $this->logger->getTotalLogs(),
            'size_bytes' => (int) $this->logger->getLogSizeInBytes(), 'pro_active' => $this->wordpress->pro(), 'timezone' => $this->wordpress->timezone()];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return array<array-key, mixed>
     */
    public function listing(array $input): array
    {
        $limit = $input['per_page'] ?? 20;
        $page = $input['page'] ?? 1;
        $query = ['group' => CronToWooActionSchedulerAdapter::SCHEDULED_ACTION_GROUP,
            'status' => $input['status'] ?? 'pending', 'per_page' => $limit, 'offset' => ($page - 1) * $limit,
            'orderby' => 'action_id', 'order' => 'ASC'];
        $count = $this->store->query_actions($query, 'count');
        $ids = $this->store->query_actions($query);
        if (!is_numeric($count) || !is_array($ids)) {
            throw new \RuntimeException('The native scheduler query failed.');
        }
        $total = (int) $count;
        $items = [];
        foreach ($ids as $id) {
            $state = $this->get(['id' => (int) $id]);
            if (!($state instanceof \WP_Error)) {
                $items[] = $state;
            }
        }
        return ['items' => $items, 'page' => $page, 'total_pages' => (int) ceil($total / $limit)];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function get(array $input)
    {
        try {
            $action = $this->store->fetch_action($input['id']);
            if (
                $action->get_group() !== CronToWooActionSchedulerAdapter::SCHEDULED_ACTION_GROUP
                || !in_array($action->get_hook(), [ExpiratorHooks::ACTION_RUN_WORKFLOW, WorkflowHooks::ACTION_WORKFLOW_ENGINE_EXECUTE_SCHEDULED_STEP], true)
            ) {
                return Registry::error(__('Future scheduled action not found.', 'post-expirator'), 404);
            }
            $postId = $workflowId = 0;
            if ($action->get_hook() === ExpiratorHooks::ACTION_RUN_WORKFLOW) {
                $args = $action->get_args();
                $postId = (int) ($args['postId'] ?? ($args[0]['postId'] ?? 0));
            } else {
                $step = ($this->stepFactory)();
                $step->loadByActionId($input['id']);
                $postId = $step->getPostId();
                $workflowId = $step->getWorkflowId();
            }
            if (
                ($postId && (!$this->wordpress->post($postId) || !$this->wordpress->can('edit_post', $postId)
                    || !$this->wordpress->can(ExpiratorCapabilities::EXPIRE_POST)))
                || ($workflowId && (!$this->wordpress->can('edit_post', $workflowId) || !$this->wordpress->can(WorkflowCapabilities::EDIT_WORKFLOWS)))
                || (!$postId && !$workflowId)
            ) {
                return Registry::error(__('You cannot access the objects related to this action.', 'post-expirator'), 403);
            }
            $scheduledAt = $action->get_schedule()->next();
            $value = ['id' => $input['id'], 'hook' => $action->get_hook(), 'status' => $this->store->get_status($input['id']),
                'scheduled_at' => $scheduledAt ? $scheduledAt->format('c') : null, 'post_id' => $postId, 'workflow_id' => $workflowId];
            return ['action' => (object) $value, 'hash' => $this->wordpress->hash([$value, $action->get_args()]), 'dry_run' => false];
        } catch (\Throwable $error) {
            return Registry::error(__('Future scheduled action not found.', 'post-expirator'), 404);
        }
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function cancel(array $input)
    {
        return $this->operate($input, false);
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function run(array $input)
    {
        return $this->operate($input, true);
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    private function operate(array $input, bool $run)
    {
        $before = $this->get($input);
        if ($before instanceof \WP_Error) {
            return $before;
        }
        if ($before['hash'] !== $input['expected_hash'] || $before['action']->status !== 'pending') {
            return Registry::error(__('This pending action changed. Read it again before continuing.', 'post-expirator'), 409);
        }
        if ($run && !$this->wordpress->can(WorkflowCapabilities::PUBLISH_WORKFLOWS) && $before['action']->workflow_id) {
            return Registry::error(__('You cannot run this workflow.', 'post-expirator'), 403);
        }
        $simple = $before['action']->hook === ExpiratorHooks::ACTION_RUN_WORKFLOW;
        $postState = $simple ? $this->schedules->get(['post_id' => $before['action']->post_id]) : [];
        if ($simple && ($postState['action_id'] !== $input['id'] || ($run && !$this->schedules->canRun($before['action']->post_id)))) {
            return Registry::error(__('This is not the current post action, or its action permissions are denied.', 'post-expirator'), 403);
        }
        if (!empty($input['dry_run'])) {
            return array_merge($before, ['dry_run' => true]);
        }
        try {
            if ($run) {
                $this->runner->process_action($input['id'], 'PublishPress Future MCP');
            } elseif ($simple) {
                $cancelled = $this->schedules->update(['post_id' => $before['action']->post_id,
                    'expected_hash' => $postState['hash'], 'enabled' => false]);
                if ($cancelled instanceof \WP_Error) {
                    return $cancelled;
                }
            } else {
                $this->store->cancel_action($input['id']);
            }
            $saved = $this->get($input);
            if ($saved instanceof \WP_Error || $saved['action']->status === 'pending' || ($run && $saved['action']->status === 'failed')) {
                throw new \RuntimeException('Operation failed');
            }
            return $saved;
        } catch (\Throwable $error) {
            return Registry::error(__('The scheduler operation did not complete normally. Inspect its status before retrying; some effects may already have occurred.', 'post-expirator'), 500);
        }
    }
}
