<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Facade;

use PublishPress\Future\Core\HookableInterface;
use Throwable;

\defined('ABSPATH') || exit;

class HooksFacade implements HookableInterface
{
    /**
     * Add a filter hook.
     *
     * @param string $filterName
     * @param callable $callback
     * @param int $priority
     * @param int $acceptedArgs
     *
     * @return bool
     */
    public function addFilter($filterName, $callback, $priority = 10, $acceptedArgs = 1)
    {
        return \add_filter($filterName, $callback, $priority, $acceptedArgs);
    }

    /**
     * Removes a filter.
     *
     * @param string $filterName
     * @param callable $callback
     * @param integer $priority
     * @return void
     */
    public function removeFilter($filterName, $callback, $priority = 10)
    {
        return \remove_filter($filterName, $callback, $priority);
    }

    /**
     * Apply filters to the passed value.
     *
     * @param string $filterName
     * @param mixed $valueToBeFiltered
     * @param mixed ...$args
     * @return mixed
     */
    public function applyFilters($filterName, $valueToBeFiltered, ...$args)
    {
        try {
            $params = array_merge([
                $filterName,
                $valueToBeFiltered,
            ], $args);

            return call_user_func_array('apply_filters', $params);
        } catch (Throwable $e) {
            $this->logError($e, sprintf('Error applying filter %s', $filterName));

            return $valueToBeFiltered;
        }
    }

    /**
     * Add an action hook.
     *
     * @param string $actionName
     * @param callable $callback
     * @param int $priority
     * @param int $acceptedArgs
     *
     * @return bool
     */
    public function addAction($actionName, $callback, $priority = 10, $acceptedArgs = 1)
    {
        return \add_action($actionName, $callback, $priority, $acceptedArgs);
    }

    /**
     * Removes an action.
     *
     * @param string $actionName
     * @param callable $callback
     * @param integer $priority
     * @return void
     */
    public function removeAction($actionName, $callback, $priority = 10)
    {
        return \remove_action($actionName, $callback, $priority);
    }

    /**
     * Execute the action.
     *
     * @param string $actionName
     * @param mixed ...$args
     *
     * @return mixed
     */
    public function doAction($actionName, ...$args)
    {
        try {
            $params = array_merge([
                $actionName,
            ], $args);

            return call_user_func_array('do_action', $params);
        } catch (Throwable $e) {
            $this->logError($e, sprintf('Error executing action %s', $actionName));

            return;
        }
    }

    public static function registerActivationHook($pluginFile, $callback)
    {
        \register_activation_hook($pluginFile, $callback);
    }

    /**
     * @param string $pluginFile
     * @param callable $callback
     */
    public static function registerDeactivationHook($pluginFile, $callback)
    {
        \register_deactivation_hook($pluginFile, $callback);
    }

    public function ksesRemoveFilters()
    {
        \kses_remove_filters();
    }

    protected function logError(Throwable $e, string $message)
    {
        $message = sprintf(
            '%s: %s. File: %s:%d',
            $message,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        @error_log($message);
    }
}
