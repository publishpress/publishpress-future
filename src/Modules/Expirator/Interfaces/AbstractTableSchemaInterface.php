<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Interfaces;

\defined('ABSPATH') || exit;

/**
 * @deprecated version 3.4.3 Use the TableSchemaInterface interface instead.
 */
interface AbstractTableSchemaInterface
{
    public static function getTableName(): string;

    public static function isTableHealthy(): bool;

    public static function isTableExistent(): bool;

    public static function fixTable(): void;

    public static function createTable(): bool;

    public static function dropTable(): bool;

    public static function getErrors(): array;
}
