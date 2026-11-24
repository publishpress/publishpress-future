<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\Database\Interfaces;

\defined('ABSPATH') || exit;

interface DBTableSchemaInterface
{
    public function getTableName(): string;

    public function createTable(): bool;

    public function dropTable(): bool;

    public function isTableHealthy(): bool;

    public function isTableExistent(): bool;

    public function fixTable(): void;

    public function getErrors(): array;
}
