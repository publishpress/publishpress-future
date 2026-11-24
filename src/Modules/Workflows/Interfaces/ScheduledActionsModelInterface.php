<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface ScheduledActionsModelInterface
{
    public function deleteOrphanWorkflowArgs(): void;

    public function cancelWorkflowScheduledActions(int $workflowId): void;

    public function cancelRecurringScheduledActions(int $workflowId, string $stepId): void;

    public function deleteExpiredDoneActions(): void;

    public function hasRowWithActionUIDHash(string $actionUIDHash): bool;

    /**
     * @since 4.3.1
     */
    public function getActionIdByActionUIDHash(string $actionUIDHash): ?int;

    /**
     * @since 4.3.1
     */
    public function cancelActionById(int $actionId): void;

    /**
     * @since 4.3.2
     */
    public function getPastDuePendingActions(): array;

    /**
     * @since 4.3.2
     */
    public function cancelByWorkflowAndPostId(int $workflowId, int $postId): void;

    /**
     * @since 4.7.0
     */
    public function workflowHasScheduledActions(int $workflowId): bool;
}
