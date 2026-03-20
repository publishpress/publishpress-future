<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\Database;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;
use PublishPress\Future\Modules\Settings\HooksAbstract as SettingsHooksAbstract;

defined('ABSPATH') or die('Direct access not allowed.');

/**
 * Runs a full repair of plugin-owned tables (create/update columns and indexes).
 * Used on activation and from Settings → Diagnostics "Try to Fix Database".
 */
class DatabaseSchemaMaintainer
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var DBTableSchemaInterface
     */
    private $actionArgsSchema;

    /**
     * @var DBTableSchemaInterface
     */
    private $debugLogSchema;

    /**
     * @var DBTableSchemaInterface
     */
    private $workflowScheduledStepsSchema;

    public function __construct(
        HookableInterface $hooks,
        DBTableSchemaInterface $actionArgsSchema,
        DBTableSchemaInterface $debugLogSchema,
        DBTableSchemaInterface $workflowScheduledStepsSchema
    ) {
        $this->hooks = $hooks;
        $this->actionArgsSchema = $actionArgsSchema;
        $this->debugLogSchema = $debugLogSchema;
        $this->workflowScheduledStepsSchema = $workflowScheduledStepsSchema;
    }

    public function repairAllSchemas(): void
    {
        $this->actionArgsSchema->fixTable();
        $this->debugLogSchema->fixTable();
        $this->workflowScheduledStepsSchema->fixTable();
        $this->hooks->doAction(SettingsHooksAbstract::ACTION_FIX_DB_SCHEMA);
        DBTableSchemaHandler::clearTableExistenceCache();
    }
}
