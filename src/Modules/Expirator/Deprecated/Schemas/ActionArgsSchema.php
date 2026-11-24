<?php

namespace PublishPress\Future\Modules\Expirator\Deprecated\Schemas;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;
use PublishPress\Future\Modules\Expirator\Interfaces\AbstractTableSchemaInterface;

\defined('ABSPATH') || exit;

/**
 * @deprecated version 3.4.3 Use the DBTableSchemas/ActionArgsSchema class instead.
 */
abstract class ActionArgsSchema implements AbstractTableSchemaInterface
{
    public const HEALTH_ERROR_TABLE_DOES_NOT_EXIST = 'table_does_not_exist';
    public const HEALTH_ERROR_COLUMN_ARGS_LENGTH_NOT_UPDATED = 'column_args_length_not_updated';

    private static $schemaErrors = [];

    /**
     * @var DBTableSchemaInterface
     */
    private static $schema;

    private static function getSchema(): DBTableSchemaInterface
    {
        if (! isset(static::$schema)) {
            $container = Container::getInstance();
            static::$schema = $container->get(ServicesAbstract::DB_TABLE_ACTION_ARGS_SCHEMA);
        }

        return static::$schema;
    }

    public static function getTableName(): string
    {
        return static::getSchema()->getTableName();
    }

    public static function createTable(): bool
    {
        return static::getSchema()->createTable();
    }

    public static function dropTable(): bool
    {
        return static::getSchema()->dropTable();
    }

    public static function isTableHealthy(): bool
    {
        return static::getSchema()->isTableHealthy();
    }

    public static function isTableExistent(): bool
    {
        return static::getSchema()->isTableExistent();
    }

    public static function fixTable(): void
    {
        static::getSchema()->fixTable();
    }

    public static function getErrors(): array
    {
        return static::getSchema()->getErrors();
    }

    // Deprecated methods

    /**
     * @deprecated 3.1.4 Use isTableExistent() instead.
     */
    public static function tableExists()
    {
        return self::isTableExistent();
    }

    /**
     * @deprecated 3.4.3 Use isTableHealthy() instead.
     */
    public static function healthCheckTableExists(): bool
    {
        return self::isTableExistent();
    }

    /**
     * @deprecated 3.4.3 Use isHealthy() instead.
     */
    public static function checkSchemaHealth(): bool
    {
        return self::isTableHealthy();
    }

    /**
     * @deprecated 3.4.3 Use isTableExistent and createTable() instead.
     */
    public static function createTableIfNotExists()
    {
        if (self::isTableExistent()) {
            return;
        }

        self::createTable();
    }

    /**
     * @deprecated 3.4
     */
    public static function fixSchema()
    {
        self::fixTable();
    }

    /**
     * @deprecated 3.4.3 Use getErrors() instead.
     */
    public static function getSchemaHealthErrors(): array
    {
        return self::getErrors();
    }
}
