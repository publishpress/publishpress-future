<?php

/**
 * Utility methods for detecting third-party plugin availability.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace PublishPress\Future\Framework\WordPress\Utils;

defined('ABSPATH') or die('Direct access not allowed.');

/**
 * Static helpers for detecting whether supported third-party plugins are active.
 *
 * @since 4.10.4
 */
class ThirdPartyPluginsUtil
{
    /**
     * Checks whether Advanced Custom Fields is active.
     *
     * @return bool True if ACF is active.
     * @since 4.10.4
     */
    public static function isAcfActive(): bool
    {
        return function_exists('acf');
    }

    /**
     * Checks whether ACF Extended (ACFE) is active.
     *
     * @return bool True if ACFE is active.
     * @since 4.10.4
     */
    public static function isAcfeActive(): bool
    {
        return function_exists('acfe_get_form');
    }
}
