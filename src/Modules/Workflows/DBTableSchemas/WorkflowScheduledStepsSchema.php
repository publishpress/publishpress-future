<?php

namespace PublishPress\Future\Modules\Workflows\DBTableSchemas;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaHandlerInterface;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;

\defined('ABSPATH') || exit;

class WorkflowScheduledStepsSchema implements DBTableSchemaInterface
{
    public const HEALTH_ERROR_TABLE_DOES_NOT_EXIST = 'table_does_not_exist';
    public const HEALTH_ERROR_INVALID_INDEX = 'invalid_index';
    public const HEALTH_ERROR_INVALID_COLUMN = 'invalid_column';

    /**
     * @var DBTableSchemaHandlerInterface
     */
    private $handler;

    /**
     * @var HookableInterface
     */
    private $hooks;

    public function __construct(DBTableSchemaHandlerInterface $handler, HookableInterface $hooks)
    {
        $this->handler = $handler;
        $this->handler->setTableName('ppfuture_workflow_scheduled_steps');

        $this->hooks = $hooks;
    }

    public function getTableName(): string
    {
        return $this->handler->getTableName();
    }

    private function getColumns(): array
    {
        return [
            'action_id' => 'bigint(20) UNSIGNED NOT NULL',
            'workflow_id' => 'bigint(20) UNSIGNED NOT NULL',
            'step_id' => 'varchar(100) NOT NULL',
            'action_uid_hash' => 'varchar(32) NOT NULL',
            'action_uid' => 'varchar(400) NOT NULL',
            'is_recurring' => 'tinyint(1) NOT NULL DEFAULT 0',
            'repeat_until' => 'set("forever", "times", "date") NOT NULL DEFAULT "forever"',
            'repeat_times' => 'int(11) NOT NULL DEFAULT 0',
            'repeat_until_date' => 'datetime NULL',
            'repetition_number' => 'int(11) NOT NULL DEFAULT 0',
            'uncompressed_args' => 'varchar(10000) NULL',
            'compressed_args' => 'blob NULL',
            'is_compressed' => 'tinyint(1) NOT NULL DEFAULT 0',
            'created_at' => 'datetime NOT NULL DEFAULT CURRENT_TIMESTAMP',
            'post_id' => 'bigint(20) UNSIGNED NULL',
        ];
    }

    private function getIndexes(): array
    {
        return [
            'PRIMARY' => ['action_id'],
            'workflow_id' => ['workflow_id', 'action_id'],
            'step_id' => ['step_id', 'action_id'],
            'action_uid_hash' => ['action_uid_hash', 'action_id'],
            'is_recurring' => ['is_recurring', 'action_id'],
            'post_id' => ['post_id', 'workflow_id', 'action_id'],
        ];
    }

    public function createTable(): bool
    {
        return $this->handler->createTable($this->getColumns(), $this->getIndexes());
    }

    public function dropTable(): bool
    {
        return $this->handler->dropTable();
    }

    public function isTableHealthy(): bool
    {
        $this->handler->resetErrors();

        if (! $this->isTableExistent()) {
            $tablePrefix = $this->handler->getTablePrefix();

            $this->handler->registerError(
                self::HEALTH_ERROR_TABLE_DOES_NOT_EXIST,
                sprintf(
                    /* translators: %s is the database table name. */
                    __(
                        'The table %s does not exist.',
                        'post-expirator'
                    ),
                    $tablePrefix . $this->getTableName()
                )
            );

            // Table do not exists, we don't need to check columns and indexes.
            return false;
        }

        $columnsErrors = $this->handler->checkTableColumns($this->getColumns());
        if (! empty($columnsErrors)) {
            foreach ($columnsErrors as $columnError) {
                $this->handler->registerError(
                    self::HEALTH_ERROR_INVALID_COLUMN,
                    $columnError
                );
            }
        }

        $indexesErrors = $this->handler->checkTableIndexes($this->getIndexes());
        if (! empty($indexesErrors)) {
            foreach ($indexesErrors as $indexError) {
                $this->handler->registerError(
                    self::HEALTH_ERROR_INVALID_INDEX,
                    $indexError
                );
            }
        }

        return false === $this->handler->hasErrors();
    }

    public function isTableExistent(): bool
    {
        return $this->handler->isTableExistent();
    }

    public function getErrors(): array
    {
        return $this->handler->getErrors();
    }

    public function fixTable(): void
    {
        if (! $this->isTableExistent()) {
            $this->createTable();
        }

        if (! empty($this->handler->checkTableColumns($this->getColumns()))) {
            $this->handler->fixColumns($this->getColumns());
        }

        if (! empty($this->handler->checkTableIndexes($this->getIndexes()))) {
            $this->handler->fixIndexes($this->getIndexes());
        }
    }
}
