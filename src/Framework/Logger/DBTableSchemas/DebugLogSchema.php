<?php

namespace PublishPress\Future\Framework\Logger\DBTableSchemas;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaHandlerInterface;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;

defined('ABSPATH') or die('Direct access not allowed.');

class DebugLogSchema implements DBTableSchemaInterface
{
    public const HEALTH_ERROR_TABLE_DOES_NOT_EXIST = 'table_does_not_exist';
    public const HEALTH_ERROR_INVALID_INDEX = 'invalid_index';

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
        $this->handler->setTableName('postexpirator_debug');

        $this->hooks = $hooks;
    }

    public function getTableName(): string
    {
        return $this->handler->getTableName();
    }

    private function getColumns(): array
    {
        return [
            'id' => 'int(9) NOT NULL AUTO_INCREMENT',
            'timestamp' => 'datetime(3) NOT NULL',
            'blog' => 'int(9) NOT NULL',
            'request_id' => "varchar(32) DEFAULT ''",
            'trigger_activated' => "tinyint(1) NOT NULL DEFAULT 0",
            'message' => "text NOT NULL",
        ];
    }

    private function getIndexes(): array
    {
        return [
            'PRIMARY' => ['id'],
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
                    __(
                        'The table %s does not exist.',
                        'post-expirator'
                    ),
                    $this->getTableName()
                )
            );
        }

        $indexesErrors = $this->handler->checkTableIndexes($this->getIndexes());
        if (! empty($indexesErrors)) {
            $this->handler->registerError(
                self::HEALTH_ERROR_INVALID_INDEX,
                __(
                    'The table indexes are invalid: ',
                    'post-expirator'
                ) . implode(', ', $indexesErrors)
            );
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

        if ($this->isTableExistent()) {
            $this->handler->fixColumns($this->getColumns());
        }

        if (! empty($this->handler->checkTableIndexes($this->getIndexes()))) {
            $this->handler->fixIndexes($this->getIndexes());
        }
    }

    /**
     * Add request_id column to the table if it does not exist.
     *
     * @since 4.10.0
     * @return void
     */
    public function addRequestIdColumnIfMissing(): void
    {
        if (! $this->isTableExistent()) {
            return;
        }

        $columns = $this->handler->getTableColumns();
        if (in_array('request_id', $columns, true)) {
            return;
        }

        $this->handler->addColumn('request_id', "varchar(32) DEFAULT ''");
    }

    /**
     * Add trigger_activated column to the table if it does not exist.
     *
     * @since 4.10.0
     * @return void
     */
    public function addTriggerActivatedColumnIfMissing(): void
    {
        if (! $this->isTableExistent()) {
            return;
        }

        $columns = $this->handler->getTableColumns();
        if (in_array('trigger_activated', $columns, true)) {
            return;
        }

        $this->handler->addColumn('trigger_activated', "tinyint(1) NOT NULL DEFAULT 0");
    }

    /**
     * Add millisecond precision to the timestamp column if missing.
     *
     * @since 4.10.0
     * @return void
     */
    public function addTimestampMillisecondsSupport(): void
    {
        if (! $this->isTableExistent()) {
            return;
        }

        $this->handler->changeColumn('timestamp', 'datetime(3) NOT NULL');
    }
}
