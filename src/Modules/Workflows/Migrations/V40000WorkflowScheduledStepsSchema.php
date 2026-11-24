<?php

namespace PublishPress\Future\Modules\Workflows\Migrations;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;
use PublishPress\Future\Modules\Expirator\Interfaces\MigrationInterface;
use PublishPress\Future\Modules\Workflows\HooksAbstract;

\defined('ABSPATH') || exit;

class V40000WorkflowScheduledStepsSchema implements MigrationInterface
{
    public const HOOK = HooksAbstract::ACTION_MIGRATE_WORKFLOW_SCHEDULED_STEPS_SCHEMA;

    private $hooksFacade;

    /**
     * @var DBTableSchemaInterface
     */
    private $workflowScheduledStepsSchema;

    /**
     * @param \PublishPress\Future\Core\HookableInterface $hooksFacade
     */
    public function __construct(
        HookableInterface $hooksFacade,
        DBTableSchemaInterface $workflowScheduledStepsSchema
    ) {
        $this->hooksFacade = $hooksFacade;
        $this->workflowScheduledStepsSchema = $workflowScheduledStepsSchema;

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
        if (!$this->workflowScheduledStepsSchema->isTableExistent()) {
            $this->workflowScheduledStepsSchema->createTable();
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
            return __('Migrate workflow scheduled steps schema after v4.0.0', 'post-expirator');
        }

        return $text;
    }
}
