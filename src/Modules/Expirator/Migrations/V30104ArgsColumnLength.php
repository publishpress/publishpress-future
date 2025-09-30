<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Migrations;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;
use PublishPress\Future\Modules\Expirator\Interfaces\MigrationInterface;

use function tad\WPBrowser\vendorDir;

defined('ABSPATH') or die('Direct access not allowed.');

class V30104ArgsColumnLength implements MigrationInterface
{
    public const HOOK = ExpiratorHooks::ACTION_MIGRATE_ARGS_LENGTH;

    private $hooksFacade;

    /**
     * @var DBTableSchemaInterface
     */
    private $actionArgsSchema;

    /**
     * @param \PublishPress\Future\Core\HookableInterface $hooksFacade
     */
    public function __construct(
        HookableInterface $hooksFacade,
        DBTableSchemaInterface $actionArgsSchema
    ) {
        $this->hooksFacade = $hooksFacade;
        $this->actionArgsSchema = $actionArgsSchema;

        // TODO: Move this for a migration scheduler instead of add itself?
        $this->hooksFacade->addAction(self::HOOK, [$this, 'migrate']);
        $this->hooksFacade->addAction(
            ExpiratorHooks::FILTER_ACTION_SCHEDULER_LIST_COLUMN_HOOK,
            [$this, 'formatLogActionColumn'],
            10,
            2
        );
    }

    public function migrate()
    {
        $this->changeArgsColumnLength();
    }

    /**
     * @param string $text
     * @param array $row
     * @return string
     */
    public function formatLogActionColumn($text, $row)
    {
        if ($row['hook'] === self::HOOK) {
            return __(
                'Change args column length to 1000 in the table _ppfuture_actions_args after v3.1.4',
                'post-expirator'
            );
        }

        return $text;
    }

    private function changeArgsColumnLength()
    {
        global $wpdb;

        $tableName = $this->actionArgsSchema->getTableName();

        // TODO: Use the db table schema here?
        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
        $wpdb->query("ALTER TABLE `$tableName` MODIFY COLUMN args varchar(1000) NOT NULL");
    }
}
