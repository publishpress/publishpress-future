<?php

/**
 * @Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Interfaces;

\defined('ABSPATH') || exit;

interface CronInterface
{
    /**
     * @return string
     */
    public function getIdentifier();

    /**
     * @param string $action
     * @param array $args
     * @param bool $clearOnlyPendingActions
     * @return int|null
     */
    public function clearScheduledAction($action, $args = [], $clearOnlyPendingActions = false);

    /**
     * @param string $action
     * @param array $args
     * @return false|int
     */
    public function getNextScheduleForAction($action, $args = []);

    /**
     * @param int $timestamp
     * @param string $hook
     * @param array $args
     * @return int
     */
    public function scheduleSingleAction(
        $timestamp,
        $hook,
        $args = [],
        $unique = false,
        $priority = 10
    );

    /**
     * @param int $timestamp
     * @param int $intervalInSeconds
     * @param string $hook
     * @param array $args
     * @param boolean $unique
     * @param integer $priority
     * @return int
     */
    public function scheduleRecurringActionInSeconds(
        $timestamp,
        $intervalInSeconds,
        $hook,
        $args = [],
        $unique = false,
        $priority = 10
    );

    /**
     * @param int $timestamp
     * @param string $schedule
     * @param string $hook
     * @param array $args
     * @param boolean $unique
     * @param integer $priority
     * @return int
     */
    public function scheduleRecurringAction(
        $timestamp,
        $schedule,
        $hook,
        $args = [],
        $unique = false,
        $priority = 10
    );

    /**
     * @param STRING $hook
     * @param array $args
     * @param boolean $unique
     * @param integer $priority
     * @return int
     */
    public function scheduleAsyncAction(
        $hook,
        $args = [],
        $unique = false,
        $priority = 10
    );

    /**
     * @param int $postId
     * @return true
     */
    public function postHasScheduledActions($postId);

    /**
     * @param string $hook
     * @return array
     */
    public function getScheduledActions($hook);

    /**
     * @param string $action
     * @param array $args
     * @param bool $unique
     * @return int
     */
    public function enqueueAsyncAction($action, $args = [], $unique = false);

    /**
     * @param string $group
     * @return mixed
     */
    public function cancelActionsByGroup($group);
}
