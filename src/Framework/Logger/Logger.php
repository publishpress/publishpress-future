<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\Logger;

use PublishPress\Future\Framework\Logger\LogLevelAbstract as LogLevel;
use PublishPress\Future\Framework\WordPress\Facade\DatabaseFacade;
use PublishPress\Future\Framework\WordPress\Facade\SiteFacade;
use PublishPress\Future\Modules\Settings\SettingsFacade;

defined('ABSPATH') or die('Direct access not allowed.');

class Logger implements LoggerInterface
{
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

        $this->initialize();
    }

    private function initialize()
    {
        if ($this->databaseTableDoNotExists()) {
            $this->createDatabaseTable();
        }
    }

    private function databaseTableDoNotExists()
    {
        $databaseTableName = $this->getDatabaseTableName();

        return $this->db->getVar("SHOW TABLES LIKE '$databaseTableName'") !== $this->dbTableName;
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
            `timestamp` TIMESTAMP NOT NULL,
            `blog` INT(9) NOT NULL,
            `request_id` varchar(32) DEFAULT '',
            `trigger_activated` tinyint(1) NOT NULL DEFAULT 0,
            `message` text NOT NULL
        );";

        $this->db->modifyStructure($tableStructure);
    }

    public function deleteLogs()
    {
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
        return is_admin() && isset($_GET['action']) && $_GET['action'] === 'publishpress_future_debug_log';
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
        if (! $this->debugIsEnabled()) {
            return;
        }

        // Do not log when downloading the log itself.
        if ($this->isDownloadLogRequested()) {
            return;
        }

        $levelDescription = strtoupper($level);

        $databaseTableName = $this->getDatabaseTableName();

        $fullMessage = sprintf('%s: %s', $levelDescription, $message);

        if (! empty($context)) {
            $fullMessage .= '[' . implode(', ', $context) . ']';
        }

        $this->db->query(
            $this->db->prepare(
                "INSERT INTO $databaseTableName (`timestamp`,`blog`,`request_id`,`trigger_activated`,`message`) VALUES (FROM_UNIXTIME(%d),%s,%s,0,%s)",
                time(),
                $this->site->getBlogId(),
                $this->requestId,
                $fullMessage
            )
        );
    }

    /**
     * @return bool
     */
    private function debugIsEnabled()
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
     * Mark the current request as having a workflow trigger activated.
     *
     * @since 4.9.5
     * @return void
     */
    public function markCurrentRequestHasTriggerActivated(): void
    {
        if (! $this->debugIsEnabled()) {
            return;
        }

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
        $databaseTableName = $this->getDatabaseTableName();
        $limit = absint($limit);
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
     */
    public function getTotalLogs()
    {
        $databaseTableName = $this->getDatabaseTableName();

        return (int)$this->db->getVar("SELECT COUNT(*) FROM $databaseTableName");
    }

    /**
     * @inheritDoc
     */
    public function getLogSizeInBytes()
    {
        $databaseTableName = $this->getDatabaseTableName();

        return $this->db->getVar("SELECT SUM(LENGTH(`message`)) FROM $databaseTableName");
    }

    /**
     * @return void
     */
    public function dropDatabaseTable()
    {
        $databaseTableName = $this->getDatabaseTableName();

        $this->db->dropTable($databaseTableName);
    }
}
