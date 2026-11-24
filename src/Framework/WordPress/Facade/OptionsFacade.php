<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Facade;

use function PublishPress\Future\Framework\WordPress\get_option;

\defined('ABSPATH') || exit;

class OptionsFacade
{
    public function initialize()
    {
    }

    /**
     * @param string $optionName
     *
     * @return bool
     */
    public function deleteOption($optionName)
    {
        return \delete_option($optionName);
    }

    /**
     * @param string $optionName
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    public function getOption($optionName, $defaultValue = false)
    {
        return \get_option($optionName, $defaultValue);
    }

    /**
     * @param string $optionName
     * @param mixed $newValue
     * @param string|bool $autoLoad
     * @return bool
     */
    public function updateOption($optionName, $newValue, $autoLoad = null)
    {
        return \update_option($optionName, $newValue, $autoLoad);
    }

    /**
     * @param string $optionName
     * @param mixed $newValue
     * @return bool
     */
    public function addOption($optionName, $newValue)
    {
        return \add_option($optionName, $newValue);
    }

    /**
     * @param string $prefix
     * @return array
     */
    public function getOptionsWithPrefix($prefix)
    {
        global $wpdb;
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
        $options = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE %s",
                sanitize_key($prefix) . '%'
            )
        );
        $result = [];
        foreach ($options as $option) {
            $result[$option->option_name] = $option->option_value;
        }
        return $result;
    }
}
