<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use Exception;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaInterface;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowScheduledStepModelInterface;

\defined('ABSPATH') || exit;

class WorkflowScheduledStepModel implements WorkflowScheduledStepModelInterface
{
    public const META_TOTAL_RUN_COUNT_PREFIX = '_pp_workflow_step_run_count_';

    public const META_LAST_RUN_AT_PREFIX = '_pp_workflow_step_last_run_at_';

    public const META_FINISHED_PREFIX = '_pp_workflow_step_finished_';

    /**
     * @var int
     */
    private $actionId;

    /**
     * @var int
     */
    private $workflowId;

    /**
     * @var string
     */
    private $stepId;

    /**
     * @var string
     */
    private $actionUID;

    /**
     * @var string
     */
    private $actionUIDHash;

    /**
     * @var bool
     */
    private $isRecurring = false;

    /**
     * @var string
     */
    private $repeatUntil = 'forever';

    /**
     * @var int
     */
    private $repeatTimes = 0;

    /**
     * @var string
     */
    private $repeatUntilDate = '0000-00-00 00:00:00';

    /**
     * @var int
     */
    private $repetitionNumber = 0;

    /**
     * @var int
     */
    private $totalRunCount = 0;

    /**
     * @var string
     */
    private $lastRunAt = '0000-00-00 00:00:00';

    /**
     * @var bool
     */
    private $isCompressed = false;

    /**
     * @var int
     */
    private $postId = 0;

    /**
     * @var array
     */
    private $args;

    /**
     * @var bool
     */
    private $isFinished = null;

    public function setActionId(int $actionId): void
    {
        $this->actionId = $actionId;
    }

    public function getActionId(): int
    {
        return (int) $this->actionId;
    }

    public function setWorkflowId(int $workflowId): void
    {
        $this->workflowId = $workflowId;
    }

    public function getWorkflowId(): int
    {
        return (int) $this->workflowId;
    }

    public function setStepId(string $stepId): void
    {
        $this->stepId = $stepId;
    }

    public function getStepId(): string
    {
        return $this->stepId;
    }

    public function setActionUID(string $actionUID): void
    {
        $this->actionUID = $actionUID;
        $this->actionUIDHash = md5($actionUID);
    }

    public function getActionUID(): string
    {
        return $this->actionUID;
    }

    public function getActionUIDHash(): string
    {
        return $this->actionUIDHash;
    }

    public function setIsRecurring(bool $isRecurring): void
    {
        $this->isRecurring = $isRecurring;
    }

    public function getIsRecurring(): bool
    {
        return $this->isRecurring;
    }

    public function setRepeatUntil(string $repeatUntil): void
    {
        $this->repeatUntil = $repeatUntil;
    }

    public function getRepeatUntil(): string
    {
        return $this->repeatUntil;
    }

    public function setRepeatTimes(int $repeatTimes): void
    {
        $this->repeatTimes = $repeatTimes;
    }

    public function getRepeatTimes(): int
    {
        return (int) $this->repeatTimes;
    }

    public function setRepeatUntilDate(string $repeatUntilDate): void
    {
        $this->repeatUntilDate = $repeatUntilDate;
    }

    public function getRepeatUntilDate(): string
    {
        return $this->repeatUntilDate;
    }

    private function getTotalRunCountMetaKey(): string
    {
        return self::META_TOTAL_RUN_COUNT_PREFIX . $this->getActionUIDHash();
    }

    private function getLastRunAtMetaKey(): string
    {
        return self::META_LAST_RUN_AT_PREFIX . $this->getActionUIDHash();
    }

    public function setTotalRunCount(int $totalRunCount): void
    {
        $this->totalRunCount = $totalRunCount;

        update_post_meta($this->getWorkflowId(), $this->getTotalRunCountMetaKey(), $totalRunCount);
    }

    public function getTotalRunCount(): int
    {
        return (int) get_post_meta($this->getWorkflowId(), $this->getTotalRunCountMetaKey(), true);
    }

    public function setLastRunAt(string $lastRunAt): void
    {
        $this->lastRunAt = $lastRunAt;

        update_post_meta($this->getWorkflowId(), $this->getLastRunAtMetaKey(), $lastRunAt);
    }

    public function getLastRunAt(): string
    {
        return get_post_meta($this->getWorkflowId(), $this->getLastRunAtMetaKey(), true);
    }

    public function setPostId(int $postId): void
    {
        $this->postId = $postId;
    }

    public function getPostId(): int
    {
        return (int) $this->postId;
    }

    public function resetRunData(): void
    {
        $this->setTotalRunCount(0);
        $this->setLastRunAt('0000-00-00 00:00:00');
    }

    public function setIsCompressed(bool $isCompressed): void
    {
        $this->isCompressed = $isCompressed;
    }

    public function getIsCompressed(): bool
    {
        return $this->isCompressed;
    }

    public function setArgs(array $args): void
    {
        $this->args = $args;
    }

    public function getArgs(): array
    {
        return $this->args;
    }

    public function insert(): bool
    {
        global $wpdb;

        $row = $this->getRow();

        if ($this->checkIfExists()) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $insertedCount = $wpdb->insert(
            $this->getTableSchema()->getTableName(),
            $row
        );

        if ($insertedCount === false) {
            throw new Exception(esc_html('Error saving workflow scheduled step: ' . $wpdb->last_error));
        }

        return true;
    }

    private function checkIfExists(): bool
    {
        global $wpdb;

        $actionId = $this->getActionId();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $exists = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM %i WHERE action_id = %d",
                $this->getTableSchema()->getTableName(),
                $actionId
            ),
            ARRAY_A
        );

        return ! empty($exists);
    }

    public function update(): bool
    {
        global $wpdb;

        $row = $this->getRow();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $updatedCount = $wpdb->update(
            $this->getTableSchema()->getTableName(),
            $row,
            ['action_id' => $this->getActionId()]
        );

        if ($updatedCount === false) {
            throw new Exception(esc_html('Error updating workflow scheduled step: ' . $wpdb->last_error));
        }

        return true;
    }

    public function delete(): bool
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $deletedCount = $wpdb->delete(
            $this->getTableSchema()->getTableName(),
            ['action_id' => $this->getActionId()]
        );
        if ($deletedCount === false) {
            throw new Exception(esc_html('Error deleting workflow scheduled step: ' . $wpdb->last_error));
        }

        return true;
    }

    public function loadByActionId(int $id): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching
        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM %i WHERE action_id = %d",
                $this->getTableSchema()->getTableName(),
                $id
            ),
            ARRAY_A
        );

        if (empty($row)) {
            throw new Exception('Scheduled step not found');
        }

        $this->setActionId($row['action_id']);
        $this->setWorkflowId($row['workflow_id']);
        $this->setStepId($row['step_id']);
        $this->setActionUID($row['action_uid']);
        $this->setIsRecurring((int) $row['is_recurring'] === 1);
        $this->setRepeatUntil($row['repeat_until']);
        $this->setRepeatTimes($row['repeat_times']);
        $this->setRepeatUntilDate($row['repeat_until_date']);
        $this->setIsCompressed((int) $row['is_compressed'] === 1);
        $this->setPostId($row['post_id'] ?? 0);
        $this->setRepetitionNumber($row['repetition_number'] ?? 0);
        $this->isFinished = null;

        $this->setArgs($this->decodeArguments($row['uncompressed_args'] ?? []));
    }

    private function getTableSchema(): DBTableSchemaInterface
    {
        $container = Container::getInstance();

        return $container->get(ServicesAbstract::DB_TABLE_WORKFLOW_SCHEDULED_STEPS_SCHEMA);
    }

    private function getRow(): array
    {
        $row = [
            'action_id' => $this->getActionId(),
            'workflow_id' => $this->getWorkflowId(),
            'step_id' => $this->getStepId(),
            'action_uid' => $this->getActionUID(),
            'action_uid_hash' => md5($this->getActionUID()),
            'is_compressed' => $this->getIsCompressed() ? 1 : 0,
            'repeat_until' => $this->getRepeatUntil(),
            'repeat_times' => $this->getRepeatTimes(),
            'repeat_until_date' => $this->getRepeatUntilDate(),
            'is_recurring' => $this->getIsRecurring() ? 1 : 0,
            'post_id' => $this->getPostId(),
            'repetition_number' => $this->getTotalRunCount(),
            'uncompressed_args' => $this->encodeArguments($this->getArgs()),
            // We don't store compressed arguments anymore. Keeping this for backwards compatibility.
            'compressed_args' => null,
        ];

        return $row;
    }

    private function encodeArguments(array $args): string
    {
        return wp_json_encode($args);
    }

    private function decodeArguments(string $args): array
    {
        $decodedArgs = json_decode($args, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }

        if (! is_array($decodedArgs)) {
            return [];
        }

        return $decodedArgs;
    }

    public function expectCompressedArguments(): bool
    {
        // TODO: Use DI here
        $container = Container::getInstance();
        $settingsFacade = $container->get(ServicesAbstract::SETTINGS);

        return $settingsFacade->getStepScheduleCompressedArgsStatus();
    }

    public function incrementTotalRunCount(): void
    {
        $this->setTotalRunCount($this->getTotalRunCount() + 1);
    }

    public function updateLastRunAt(): void
    {
        $this->setLastRunAt(current_time('mysql'));
    }

    public function markAsFinished(): void
    {
        $this->isFinished = true;

        $workflowId = $this->getWorkflowId();
        $actionUIDHash = $this->getActionUIDHash();

        $metaKey = self::META_FINISHED_PREFIX . $actionUIDHash;
        update_post_meta($workflowId, $metaKey, 1);
    }

    public function isFinished(): bool
    {
        if (is_null($this->isFinished)) {
            $workflowId = $this->getWorkflowId();
            $actionUIDHash = $this->getActionUIDHash();

            $this->isFinished = self::getMetaIsFinished($workflowId, $actionUIDHash);
        }

        return $this->isFinished;
    }

    public static function getMetaIsFinished(int $workflowId, string $actionUIDHash): bool
    {
        $metaKey = self::META_FINISHED_PREFIX . $actionUIDHash;

        return (bool) get_post_meta($workflowId, $metaKey, true);
    }

    public static function getMetaRunCount(int $workflowId, string $actionUIDHash): int
    {
        $metaKey = self::META_TOTAL_RUN_COUNT_PREFIX . $actionUIDHash;

        return (int) get_post_meta($workflowId, $metaKey, true);
    }

    public function setRepetitionNumber(int $repetitionNumber): void
    {
        $this->repetitionNumber = $repetitionNumber;
    }

    public function getRepetitionNumber(): int
    {
        return (int) $this->repetitionNumber;
    }
}
