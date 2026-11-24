<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Facade;

use WP_Error;

\defined('ABSPATH') || exit;

class CronFacade
{
    /**
     * @param string $hook
     * @param array $args
     * @param bool $wpError
     * @return int|WP_Error|false
     */
    public function clearScheduledHook($hook, $args = [], $wpError = false)
    {
        return \wp_clear_scheduled_hook($hook, $args, $wpError);
    }

    /**
     * @param string $hook
     * @param array $args
     * @return false|int
     */
    public function getNextScheduleForHook($hook, $args = [])
    {
        return \wp_next_scheduled($hook, $args);
    }

    /**
     * @param int $timestamp
     * @param string $hook
     * @param array $args
     * @return bool|WP_Error
     */
    public function scheduleSingleEventForHook($timestamp, $hook, $args = [])
    {
        return \wp_schedule_single_event($timestamp, $hook, $args);
    }
}
