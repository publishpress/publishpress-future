<?php

/**
 * Migration to add millisecond precision to the debug log timestamp column.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

namespace PublishPress\Future\Modules\Debug\Migrations;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\DBTableSchemas\DebugLogSchema;
use PublishPress\Future\Modules\Debug\HooksAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;
use PublishPress\Future\Modules\Expirator\Interfaces\MigrationInterface;

defined('ABSPATH') or die('Direct access not allowed.');

/**
 * Migration to add millisecond precision to the debug log timestamp column.
 *
 * @since 4.10.0
 */
class V04906DebugLogTimestampMilliseconds implements MigrationInterface
{
    public const HOOK = HooksAbstract::ACTION_MIGRATE_DEBUG_LOG_TIMESTAMP_MS;

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var DebugLogSchema
     */
    private $debugLogSchema;

    /**
     * @param HookableInterface $hooks
     * @param DebugLogSchema $debugLogSchema Debug log table schema.
     */
    public function __construct(HookableInterface $hooks, DebugLogSchema $debugLogSchema)
    {
        $this->hooks = $hooks;
        $this->debugLogSchema = $debugLogSchema;

        $this->hooks->addAction(self::HOOK, [$this, 'migrate']);
        $this->hooks->addAction(
            ExpiratorHooks::FILTER_ACTION_SCHEDULER_LIST_COLUMN_HOOK,
            [$this, 'formatLogActionColumn'],
            10,
            2
        );
    }

    /**
     * Add millisecond precision to the timestamp column.
     *
     * @return void
     */
    public function migrate()
    {
        $this->debugLogSchema->addTimestampMillisecondsSupport();
    }

    /**
     * Format the migration description for Action Scheduler log display.
     *
     * @param string $text Existing column text.
     * @param array $row Action row data.
     * @return string
     */
    public function formatLogActionColumn($text, $row)
    {
        if ($row['hook'] === self::HOOK) {
            return __('Add millisecond precision to debug log timestamp column (v4.10.0)', 'post-expirator');
        }

        return $text;
    }
}
