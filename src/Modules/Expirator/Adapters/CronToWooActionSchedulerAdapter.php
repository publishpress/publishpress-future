<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Adapters;

use ActionScheduler;
use Exception;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use PublishPress\Future\Modules\Expirator\Interfaces\CronInterface;
use Throwable;

use function as_enqueue_async_action;
use function as_get_scheduled_actions;
use function as_has_scheduled_action;
use function as_next_scheduled_action;
use function as_schedule_single_action;
use function as_unschedule_action;
use function as_unschedule_all_actions;

\defined('ABSPATH') || exit;

class CronToWooActionSchedulerAdapter implements CronInterface
{
    public const SCHEDULED_ACTION_GROUP = 'publishpress-future';

    public const IDENTIFIER = 'woo-action-scheduler';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        ?LoggerInterface $logger = null
    ) {
        $this->logger = $logger;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return self::IDENTIFIER;
    }

    /**
     * @inheritDoc
     */
    public function clearScheduledAction($action, $args = [], $clearOnlyPendingActions = true)
    {
        if ($clearOnlyPendingActions) {
            return as_unschedule_action($action, $args, self::SCHEDULED_ACTION_GROUP);
        }

        // The original method only unschedule pending actions.
        if (! ActionScheduler::is_initialized(__FUNCTION__)) {
            return 0;
        }
        $params = [
            'hook'    => $action,
            'orderby' => 'date',
            'order'   => 'ASC',
            'group'   => self::SCHEDULED_ACTION_GROUP,
        ];
        if (is_array($args)) {
            $params['args'] = $args;
        }

        $actionId = ActionScheduler::store()->query_action($params);

        if ($actionId) {
            try {
                ActionScheduler::store()->cancel_action($actionId);
            } catch (Throwable $e) {
                ActionScheduler::logger()->log(
                    $actionId,
                    sprintf(
                        /* translators: %1$s is the name of the hook to be cancelled, %2$s is the exception message. */
                        __('Caught exception while cancelling action "%1$s": %2$s. File: %3$s:%4$d', 'action-scheduler'),
                        $action,
                        $e->getMessage(),
                        $e->getFile(),
                        $e->getLine()
                    )
                );

                $actionId = null;

                throw $e;
            }
        }

        return $actionId;
    }

    /**
     * @inheritDoc
     */
    public function getNextScheduleForAction($action, $args = [])
    {
        return as_next_scheduled_action($action, $args, self::SCHEDULED_ACTION_GROUP);
    }

    /**
     * @inheritDoc
     */
    public function scheduleSingleAction(
        $timestamp,
        $hook,
        $args = [],
        $unique = false,
        $priority = 10
    ) {
        return as_schedule_single_action(
            $timestamp,
            $hook,
            $args,
            self::SCHEDULED_ACTION_GROUP,
            $unique,
            $priority
        );
    }

    public function scheduleRecurringActionInSeconds(
        $timestamp,
        $intervalInSeconds,
        $hook,
        $args = [],
        $unique = false,
        $priority = 10
    ) {
        return as_schedule_recurring_action(
            $timestamp,
            $intervalInSeconds,
            $hook,
            $args,
            self::SCHEDULED_ACTION_GROUP,
            $unique,
            $priority
        );
    }

    public function scheduleRecurringAction(
        $timestamp,
        $schedule,
        $hook,
        $args = [],
        $unique = false,
        $priority = 10
    ) {

        return as_schedule_cron_action(
            $timestamp,
            $schedule,
            $hook,
            $args,
            self::SCHEDULED_ACTION_GROUP,
            $unique,
            $priority
        );
    }

    public function scheduleAsyncAction(
        $hook,
        $args = [],
        $unique = false,
        $priority = 10
    ) {
        return as_enqueue_async_action(
            $hook,
            $args,
            self::SCHEDULED_ACTION_GROUP,
            $unique,
            $priority
        );
    }

    /**
     * @param $postId
     * @return bool
     */
    public function postHasScheduledActions($postId)
    {
        $hasScheduledActions = as_has_scheduled_action(
            HooksAbstract::ACTION_RUN_WORKFLOW,
            ['postId' => $postId, 'workflow' => 'expire'],
            self::SCHEDULED_ACTION_GROUP
        );
        if (! $hasScheduledActions) {
            // Try checking with the legacy hook.
            $hasScheduledActions = as_has_scheduled_action(
                HooksAbstract::ACTION_LEGACY_RUN_WORKFLOW,
                ['postId' => $postId, 'workflow' => 'expire'],
                self::SCHEDULED_ACTION_GROUP
            );
        }

        return $hasScheduledActions;
    }

    /**
     * @param string $hook
     * @return array
     */
    public function getScheduledActions($hook)
    {
        $args = [
            'group' => self::SCHEDULED_ACTION_GROUP,
            'status' => \ActionScheduler_Store::STATUS_PENDING,
            'per_page' => -1,
        ];

        if (! empty($hook)) {
            $args['hook'] = $hook;
        }

        return as_get_scheduled_actions($args);
    }

    /**
     * @param $action
     * @param array $args
     * @param $unique
     * @return int
     */
    public function enqueueAsyncAction($action, $args = [], $unique = false)
    {
        return as_enqueue_async_action($action, $args, self::SCHEDULED_ACTION_GROUP, $unique);
    }

    /**
     * @param string $group
     * @return void
     */
    public function cancelActionsByGroup($group)
    {
        as_unschedule_all_actions('', [], $group);
    }
}
