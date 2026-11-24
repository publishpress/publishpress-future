<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Core;

\defined('ABSPATH') || exit;

interface HookableInterface
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
    public function addFilter($filterName, $callback, $priority = 10, $acceptedArgs = 1);

    /**
     * Apply filters to the passed value.
     *
     * @param string $filterName
     * @param mixed $valueToBeFiltered
     * @param mixed ...$args
     * @return mixed
     */
    public function applyFilters($filterName, $valueToBeFiltered, ...$args);

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
    public function addAction($actionName, $callback, $priority = 10, $acceptedArgs = 1);

    /**
     * Execute the action.
     *
     * @param string $actionName
     * @param mixed ...$args
     *
     * @return mixed
     */
    public function doAction($actionName, ...$args);

    public static function registerActivationHook($pluginFile, $callback);

    /**
     * @param string $pluginFile
     * @param callable $callback
     */
    public static function registerDeactivationHook($pluginFile, $callback);

    public function ksesRemoveFilters();

    /**
     * Removes a filter.
     *
     * @param string $filterName
     * @param callable $callback
     * @param integer $priority
     * @return void
     */
    public function removeFilter($filterName, $callback, $priority = 10);

    /**
     * Removes an action.
     *
     * @param string $actionName
     * @param callable $callback
     * @param integer $priority
     * @return void
     */
    public function removeAction($actionName, $callback, $priority = 10);
}
