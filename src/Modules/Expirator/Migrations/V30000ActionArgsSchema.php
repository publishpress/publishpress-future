<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Migrations;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;
use PublishPress\Future\Modules\Expirator\Interfaces\MigrationInterface;

\defined('ABSPATH') || exit;

class V30000ActionArgsSchema implements MigrationInterface
{
    public const HOOK = ExpiratorHooks::ACTION_MIGRATE_CREATE_ACTION_ARGS_SCHEMA;

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
        if (!$this->actionArgsSchema->isTableExistent()) {
            $this->actionArgsSchema->createTable();
        }
    }

    /**
     * @param string $text
     * @param array $row
     * @return string
     */
    public function formatLogActionColumn($text, $row)
    {
        if ($row['hook'] === self::HOOK) {
            return __('Migrate legacy actions arguments schema after v3.0.0', 'post-expirator');
        }
        return $text;
    }
}
