<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Debug;

\defined('ABSPATH') || exit;

interface DebugInterface
{
    /**
     * @param string $message
     * @return void
     */
    public function log($message);

    /**
     * @return void
     */
    public function dropDatabaseTable();

    /**
     * @return array
     */
    public function fetchAll();

    /**
     * @return array
     */
    public function fetchLatest($limit = 100);

    /**
     * @return void
     */
    public function deleteLogs();

    /**
     * @return bool
     */
    public function isEnabled();
}
