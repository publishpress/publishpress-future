<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\Logger;

defined('ABSPATH') or die('Direct access not allowed.');

interface LoggerInterface
{
    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency($message, $context = []);

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert($message, $context = []);

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical($message, $context = []);

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, $context = []);

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning($message, $context = []);

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice($message, $context = []);

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, $context = []);

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, $context = []);

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, $context = []);

    /**
     * Mark the current request as having a workflow trigger activated.
     *
     * @since 4.10.0
     * @return void
     */
    public function markCurrentRequestHasTriggerActivated();

    /**
     * @return void
     */
    public function deleteLogs();

    /**
     * @param bool $triggerActivatedOnly Filter to show only requests with trigger activated.
     * @return array<string, mixed>
     */
    public function fetchAll($triggerActivatedOnly = false);

    /**
     * @param int $limit
     * @param bool $triggerActivatedOnly Filter to show only requests with trigger activated.
     * @return array<string, mixed>
     */
    public function fetchLatest($limit = 100, $triggerActivatedOnly = false);

    /**
     * @param bool $triggerActivatedOnly Filter to count only logs from requests with trigger activated.
     * @return int
     */
    public function getTotalLogs($triggerActivatedOnly = false);

    /**
     * @param bool $triggerActivatedOnly Filter to sum only logs from requests with trigger activated.
     * @return int
     */
    public function getLogSizeInBytes($triggerActivatedOnly = false);

    /**
     * @return void
     */
    public function dropDatabaseTable();

    /**
     * @return bool
     */
    public function isDownloadLogRequested();

    /**
     * @return bool
     */
    public function isDebugEnabled();

    /**
     * Log a debug message using sprintf-style formatting.
     *
     * @param string $message Message format string.
     * @param mixed  ...$args Arguments for sprintf.
     * @return void
     * @since 4.10.0
     */
    public function debugWithArgs(string $message, ...$args): void;

    /**
     * Log an error message using sprintf-style formatting.
     *
     * @param string $message Message format string.
     * @param mixed  ...$args Arguments for sprintf.
     * @return void
     * @since 4.10.0
     */
    public function errorWithArgs(string $message, ...$args): void;

    /**
     * Log a warning message using sprintf-style formatting.
     *
     * @param string $message Message format string.
     * @param mixed  ...$args Arguments for sprintf.
     * @return void
     * @since 4.10.0
     */
    public function warningWithArgs(string $message, ...$args): void;

    /**
     * Log an info message using sprintf-style formatting.
     *
     * @param string $message Message format string.
     * @param mixed  ...$args Arguments for sprintf.
     * @return void
     * @since 4.10.0
     */
    public function infoWithArgs(string $message, ...$args): void;

    /**
     * Log a notice message using sprintf-style formatting.
     *
     * @param string $message Message format string.
     * @param mixed  ...$args Arguments for sprintf.
     * @return void
     * @since 4.10.0
     */
    public function noticeWithArgs(string $message, ...$args): void;
}
