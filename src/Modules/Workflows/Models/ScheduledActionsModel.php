<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;
use PublishPress\Future\Modules\Expirator\Adapters\CronToWooActionSchedulerAdapter;
use PublishPress\Future\Modules\Workflows\Interfaces\ScheduledActionsModelInterface;

\defined('ABSPATH') || exit;

class ScheduledActionsModel implements ScheduledActionsModelInterface
{
    public function deleteOrphanWorkflowArgs(): void
    {
        global $wpdb;

        $container = Container::getInstance();

        /**
         * @var DBTableSchemaInterface $tableSchema
         */
        $tableSchema = $container->get(ServicesAbstract::DB_TABLE_WORKFLOW_SCHEDULED_STEPS_SCHEMA);

        if (! $tableSchema->isTableExistent()) {
            return;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM %i
                WHERE NOT EXISTS(
                    SELECT 1 FROM %i
                    WHERE %i.action_id = %i.action_id
                )",
                $tableSchema->getTableName(),
                $wpdb->prefix . 'actionscheduler_actions',
                $tableSchema->getTableName(),
                $wpdb->prefix . 'actionscheduler_actions'
            )
        );
    }

    public function deleteExpiredDoneActions(): void
    {
        global $wpdb;

        $container = Container::getInstance();

        /**
         * @var SettingsFacade $settingsFacade
         */
        $settingsFacade = $container->get(ServicesAbstract::SETTINGS);

        $tableSchema = $wpdb->prefix . 'actionscheduler_actions';

        $retention = $settingsFacade->getScheduledWorkflowStepsCleanupRetention();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM %i WHERE scheduled_date_gmt < %s AND status != 'pending' AND group_id = %d",
                $tableSchema,
                gmdate('Y-m-d H:i:s', time() - ($retention * DAY_IN_SECONDS)),
                $this->getGroupID()
            )
        );

        $this->deleteOrphanWorkflowArgs();
    }

    public function hasRowWithActionUIDHash(string $actionUIDHash): bool
    {
        $actionId = $this->getActionIdByActionUIDHash($actionUIDHash);

        return !is_null($actionId);
    }

    /**
     * @since 4.3.1
     */
    public function getActionIdByActionUIDHash(string $actionUIDHash): ?int
    {
        global $wpdb;

        $container = Container::getInstance();

        /**
         * @var DBTableSchemaInterface $tableSchema
         */
        $tableSchema = $container->get(ServicesAbstract::DB_TABLE_WORKFLOW_SCHEDULED_STEPS_SCHEMA);

        if (! $tableSchema->isTableExistent()) {
            return null;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT a.*
                FROM %i AS a
                LEFT JOIN %i AS b ON a.action_id = b.action_id
                WHERE a.action_uid_hash = %s AND b.status IN (%s, %s) AND b.group_id = %d",
                $tableSchema->getTableName(),
                $wpdb->prefix . 'actionscheduler_actions',
                $actionUIDHash,
                'pending',
                'in-progress',
                $this->getGroupID()
            )
        );

        if (is_null($row)) {
            return null;
        }

        return $row->action_id;
    }

    public function cancelWorkflowScheduledActions(int $workflowId): void
    {
        global $wpdb;

        $container = Container::getInstance();

        /**
         * @var DBTableSchemaInterface $tableSchema
         */
        $tableSchema = $container->get(ServicesAbstract::DB_TABLE_WORKFLOW_SCHEDULED_STEPS_SCHEMA);

        if (! $tableSchema->isTableExistent()) {
            return;
        }

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}actionscheduler_actions AS asa
                INNER JOIN {$tableSchema->getTableName()} AS wss ON asa.action_id = wss.action_id
                SET asa.status = 'canceled'
                WHERE wss.workflow_id = %d AND asa.status = 'pending' AND asa.group_id = %d",
                $workflowId,
                $this->getGroupID()
            )
        );

        // Remove execution data for the workflow
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->postmeta}
                WHERE (
                    meta_key LIKE '" . WorkflowScheduledStepModel::META_FINISHED_PREFIX . "%%'
                    OR meta_key LIKE '" . WorkflowScheduledStepModel::META_LAST_RUN_AT_PREFIX . "%%'
                    OR meta_key LIKE '" . WorkflowScheduledStepModel::META_TOTAL_RUN_COUNT_PREFIX . "%%'
                ) AND post_id = %d",
                $workflowId
            )
        );
        // phpcs:enable
    }

    public function cancelRecurringScheduledActions(int $workflowId, string $actionUIDHash): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}actionscheduler_actions AS asa
                SET asa.status = 'canceled'
                WHERE JSON_EXTRACT(asa.extended_args, '$[0].workflowId') = %d
                    AND JSON_UNQUOTE(JSON_EXTRACT(asa.extended_args, '$[0].actionUIDHash')) = %s
                    AND asa.status = 'pending' AND asa.group_id = %d",
                $workflowId,
                $actionUIDHash,
                $this->getGroupID()
            )
        );
    }

    /**
     * @since 4.3.1
     */
    public function cancelActionById(int $actionId): void
    {
        global $wpdb;

        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$wpdb->prefix}actionscheduler_actions
                SET status = 'canceled'
                WHERE action_id = %d AND group_id = %d",
                $actionId,
                $this->getGroupID()
            )
        );
        // phpcs:enable
    }

    private function getGroupID(): int
    {
        global $wpdb;

        $groupName = CronToWooActionSchedulerAdapter::SCHEDULED_ACTION_GROUP;

        $tableSchema = $wpdb->prefix . 'actionscheduler_groups';

        $cacheKey = 'ppf_group_id_' . $groupName;
        $groupId = wp_cache_get($cacheKey);

        if (false === $groupId) {
            $groupId = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT group_id FROM %i WHERE slug = %s",
                    $tableSchema,
                    $groupName
                )
            );

            wp_cache_set($cacheKey, $groupId);
        }

        return (int) $groupId;
    }

    public function getPastDuePendingActions(): array
    {
        global $wpdb;

        $tableSchema = $wpdb->prefix . 'actionscheduler_actions';
        $groupId = $this->getGroupID();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
                FROM %i
                WHERE status = 'pending'
                    AND group_id = %d
                    AND scheduled_date_gmt < %s
                ",
                $tableSchema,
                $groupId,
                gmdate('Y-m-d H:i:s', time())
            )
        );

        return $results;
    }

    public function cancelByWorkflowAndPostId(int $workflowId, int $postId): void
    {
        global $wpdb;

        $tableSchema = $wpdb->prefix . 'actionscheduler_actions';
        $groupId = $this->getGroupID();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "UPDATE %i
                INNER JOIN %i AS wss ON wss.action_id = %i.action_id
                SET status = 'canceled'
                WHERE wss.post_id = %d
                    AND wss.workflow_id = %d
                    AND group_id = %d",
                $tableSchema,
                $wpdb->prefix . 'ppfuture_workflow_scheduled_steps',
                $tableSchema,
                $postId,
                $workflowId,
                $groupId
            )
        );
    }

    public function workflowHasScheduledActions(int $workflowId): bool
    {
        global $wpdb;

        $container = Container::getInstance();
        $tableSchema = $container->get(ServicesAbstract::DB_TABLE_WORKFLOW_SCHEDULED_STEPS_SCHEMA);

        if (!$tableSchema->isTableExistent()) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $queryCount = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*)
                FROM %i AS wss
                INNER JOIN %i AS asa ON wss.action_id = asa.action_id
                WHERE wss.workflow_id = %d AND asa.status = 'pending' AND asa.group_id = %d",
                $tableSchema->getTableName(),
                $wpdb->prefix . 'actionscheduler_actions',
                $workflowId,
                $this->getGroupID()
            )
        );

        $count = (int) $queryCount;

        return $count > 0;
    }
}
