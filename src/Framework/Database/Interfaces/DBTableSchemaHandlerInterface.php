<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\Database\Interfaces;

\defined('ABSPATH') || exit;

interface DBTableSchemaHandlerInterface
{
    public function setTableName(string $tableNameWithoutPrefix): void;

    public function getTablePrefix(): string;

    public function getTableName(): string;

    public function isTableExistent(): bool;

    public function createTable(array $columns, array $indexes): bool;

    public function dropTable(): bool;

    public function getColumnLength(string $column): int;

    public function checkTableIndexes(array $indexes): array;

    public function registerError(string $code, string $error): void;

    public function getErrors(): array;

    public function resetErrors(): void;

    public function hasErrors(): bool;

    public function fixIndexes(array $indexes): void;

    public function fixColumns(array $columns): void;

    public function getTableColumns(): array;

    public function addColumn(string $columnName, string $columnDefinition): void;

    public function getTableColumnDefinitions(): array;

    public function changeColumn(string $column, string $definition): void;

    public function checkTableColumns(array $expectedColumns): array;
}
