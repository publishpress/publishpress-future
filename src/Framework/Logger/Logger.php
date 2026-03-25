<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\Logger;

use PublishPress\Future\Framework\Database\DBTableSchemaHandler;
use PublishPress\Future\Framework\Logger\LogLevelAbstract as LogLevel;
use PublishPress\Future\Framework\WordPress\Facade\DatabaseFacade;
use PublishPress\Future\Framework\WordPress\Facade\SiteFacade;
use PublishPress\Future\Modules\Settings\SettingsFacade;

defined('ABSPATH') or die('Direct access not allowed.');

class Logger implements LoggerInterface
{
    /**
     * Tables for which ensureDebugTableExists() has already run this request.
     *
     * @var array<string, true>
     */
    private static $debugTableEnsured = [];

    /**
     * @var string
     */
    private $dbTableName;

    /**
     * @var DatabaseFacade
     */
    private $db;

    /**
     * @var SiteFacade
     */
    private $site;

    /**
     * @var SettingsFacade
     */
    private $settings;

    /**
     * @var string
     */
    private $requestId;

    public function __construct($databaseFacade, $siteFacade, $settingsFacade)
    {
        $this->db = $databaseFacade;
        $this->site = $siteFacade;
        $this->settings = $settingsFacade;

        $this->requestId = uniqid();

        // FIXME: Rename the table to something like ppfuture_debug_log and use a schema class.
        $this->dbTableName = $this->db->getTablePrefix() . 'postexpirator_debug';
    }

    /**
     * Clears lazy debug-table ensure state (test isolation; optional tooling).
     *
     * @return void
     *
     * @since 4.10.1
     */
    public static function resetDebugTableEnsureCacheForTesting(): void
    {
        self::$debugTableEnsured = [];
    }

    /**
     * Ensures the debug log table exists before the first DB access in this request.
     *
     * @return void
     *
     * @since 4.10.1
     */
    private function ensureDebugTableExists(): void
    {
        if (isset(self::$debugTableEnsured[$this->dbTableName])) {
            return;
        }

        if (! $this->debugDatabaseTableExists()) {
            $this->createDatabaseTable();
        }

        self::$debugTableEnsured[$this->dbTableName] = true;
    }

    /**
     * @return bool
     *
     * @since 4.10.1
     */
    private function debugDatabaseTableExists(): bool
    {
        $query = $this->db->prepare(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = %s AND table_name = %s LIMIT 1',
            DB_NAME,
            $this->dbTableName
        );

        $row = $this->db->getVar($query);

        return $row !== null && $row !== '';
    }

    private function getDatabaseTableName()
    {
        return $this->db->escape($this->dbTableName);
    }

    private function createDatabaseTable()
    {
        $databaseTableName = $this->getDatabaseTableName();

        $tableStructure = "CREATE TABLE `$databaseTableName` (
            `id` INT(9) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `timestamp` DATETIME(3) NOT NULL,
            `blog` INT(9) NOT NULL,
            `request_id` varchar(32) DEFAULT '',
            `trigger_activated` tinyint(1) NOT NULL DEFAULT 0,
            `message` text NOT NULL
        );";

        $this->db->modifyStructure($tableStructure);
    }

    public function deleteLogs()
    {
        $this->ensureDebugTableExists();

        $databaseTableName = $this->getDatabaseTableName();

        $this->db->query("TRUNCATE TABLE $databaseTableName");
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency($message, $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function isDownloadLogRequested()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        return \is_admin() && isset($_GET['action']) && $_GET['action'] === 'publishpress_future_debug_log';
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     * @noinspection SqlResolve
     */
    public function log($level, $message, $context = [])
    {
        if (! $this->isDebugEnabled()) {
            return;
        }

        // Do not log when downloading the log itself.
        if ($this->isDownloadLogRequested()) {
            return;
        }

        $this->ensureDebugTableExists();

        $levelDescription = strtoupper($level);

        $databaseTableName = $this->getDatabaseTableName();

        $fullMessage = sprintf('%s: %s', $levelDescription, $message);

        if (! empty($context)) {
            $fullMessage .= '[' . implode(', ', $context) . ']';
        }

        $microtime = microtime(true);
        $timestampWithMs = gmdate('Y-m-d H:i:s', (int) $microtime)
            . '.'
            . sprintf('%03d', (int) (($microtime - floor($microtime)) * 1000));

        $this->db->query(
            $this->db->prepare(
                "INSERT INTO $databaseTableName (`timestamp`,`blog`,`request_id`,`trigger_activated`,`message`) VALUES (%s,%s,%s,0,%s)",
                $timestampWithMs,
                $this->site->getBlogId(),
                $this->requestId,
                $fullMessage
            )
        );
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        return $this->settings->getDebugIsEnabled();
    }

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
    public function alert($message, $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical($message, $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);

        if (function_exists('error_log')) {
            // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
            error_log('PUBLISHPRESS FUTURE - ' . $message);
        }
    }

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
    public function warning($message, $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice($message, $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Log a debug message using sprintf-style formatting.
     *
     * @param string $message Message format string.
     * @param mixed  ...$args Arguments for sprintf.
     * @return void
     * @since 4.10.0
     */
    public function debugWithArgs(string $message, ...$args): void
    {
        if (! $this->isDebugEnabled()) {
            return;
        }

        $this->debug(sprintf($message, ...$args));
    }

    /**
     * Log an error message using sprintf-style formatting.
     *
     * @param string $message Message format string.
     * @param mixed  ...$args Arguments for sprintf.
     * @return void
     * @since 4.10.0
     */
    public function errorWithArgs(string $message, ...$args): void
    {
        $this->error(sprintf($message, ...$args));
    }

    /**
     * Log a warning message using sprintf-style formatting.
     *
     * @param string $message Message format string.
     * @param mixed  ...$args Arguments for sprintf.
     * @return void
     * @since 4.10.0
     */
    public function warningWithArgs(string $message, ...$args): void
    {
        if (! $this->isDebugEnabled()) {
            return;
        }

        $this->warning(sprintf($message, ...$args));
    }

    /**
     * Log an info message using sprintf-style formatting.
     *
     * @param string $message Message format string.
     * @param mixed  ...$args Arguments for sprintf.
     * @return void
     * @since 4.10.0
     */
    public function infoWithArgs(string $message, ...$args): void
    {
        if (! $this->isDebugEnabled()) {
            return;
        }

        $this->info(sprintf($message, ...$args));
    }

    /**
     * Log a notice message using sprintf-style formatting.
     *
     * @param string $message Message format string.
     * @param mixed  ...$args Arguments for sprintf.
     * @return void
     * @since 4.10.0
     */
    public function noticeWithArgs(string $message, ...$args): void
    {
        if (! $this->isDebugEnabled()) {
            return;
        }

        $this->notice(sprintf($message, ...$args));
    }

    /**
     * Mark the current request as having a workflow trigger activated.
     *
     * @since 4.10.0
     * @return void
     */
    public function markCurrentRequestHasTriggerActivated(): void
    {
        if (! $this->isDebugEnabled()) {
            return;
        }

        $this->ensureDebugTableExists();

        $databaseTableName = $this->getDatabaseTableName();

        $this->db->query(
            $this->db->prepare(
                "UPDATE $databaseTableName SET `trigger_activated` = 1 WHERE `request_id` = %s",
                $this->requestId
            )
        );
    }

    /**
     * @return array<string, mixed>
     * @noinspection SqlResolve
     */
    public function fetchAll($triggerActivatedOnly = false)
    {
        $this->ensureDebugTableExists();

        $databaseTableName = $this->getDatabaseTableName();
        $where = '';

        if ($triggerActivatedOnly) {
            $where = " WHERE `request_id` IN (SELECT DISTINCT `request_id` FROM $databaseTableName WHERE `trigger_activated` = 1 AND `request_id` != '')";
        }

        return (array)$this->db->getResults("SELECT * FROM $databaseTableName{$where} ORDER BY `id`", 'ARRAY_A');
    }

    /**
     * @inheritDoc
     * @param int $limit
     * @param bool $triggerActivatedOnly
     * @return array<string, mixed>
     */
    public function fetchLatest($limit = 100, $triggerActivatedOnly = false)
    {
        $this->ensureDebugTableExists();

        $databaseTableName = $this->getDatabaseTableName();
        $limit = \absint($limit);
        $where = '';

        if ($triggerActivatedOnly) {
            $where = " WHERE `request_id` IN (SELECT DISTINCT `request_id` FROM $databaseTableName WHERE `trigger_activated` = 1 AND `request_id` != '')";
        }

        $list = (array)$this->db->getResults(
            "SELECT * FROM $databaseTableName{$where} ORDER BY `id` DESC LIMIT $limit",
            'ARRAY_A'
        );

        return array_reverse($list);
    }

    /**
     * @inheritDoc
     * @param bool $triggerActivatedOnly Filter to count only logs from requests with trigger activated.
     */
    public function getTotalLogs($triggerActivatedOnly = false)
    {
        $this->ensureDebugTableExists();

        $databaseTableName = $this->getDatabaseTableName();
        $where = '';

        if ($triggerActivatedOnly) {
            $where = " WHERE `request_id` IN (SELECT DISTINCT `request_id` FROM $databaseTableName WHERE `trigger_activated` = 1 AND `request_id` != '')";
        }

        return (int)$this->db->getVar("SELECT COUNT(*) FROM $databaseTableName{$where}");
    }

    /**
     * @inheritDoc
     * @param bool $triggerActivatedOnly Filter to sum only logs from requests with trigger activated.
     */
    public function getLogSizeInBytes($triggerActivatedOnly = false)
    {
        $this->ensureDebugTableExists();

        $databaseTableName = $this->getDatabaseTableName();
        $where = '';

        if ($triggerActivatedOnly) {
            $where = " WHERE `request_id` IN (SELECT DISTINCT `request_id` FROM $databaseTableName WHERE `trigger_activated` = 1 AND `request_id` != '')";
        }

        return (int) $this->db->getVar("SELECT COALESCE(SUM(LENGTH(`message`)), 0) FROM $databaseTableName{$where}");
    }

    /**
     * @return void
     */
    public function dropDatabaseTable()
    {
        $databaseTableName = $this->getDatabaseTableName();

        $this->db->dropTable($databaseTableName);

        unset(self::$debugTableEnsured[$this->dbTableName]);
        DBTableSchemaHandler::clearTableExistenceCache();
    }
}
