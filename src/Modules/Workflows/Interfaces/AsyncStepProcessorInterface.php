<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

/**
 * @since 4.3.1 
 */
interface AsyncStepProcessorInterface extends StepProcessorInterface
{
    public function actionCallback(array $compactedArgs, array $originalArgs, bool $triggerCallbackIsRunning = false);

    public function compactArguments(string $stepSlug, string $stepId): array;

    public function expandArguments(array $compactArguments): array;

    public function cancelScheduledStep(int $actionId, array $originalArgs): void;

    public function completeScheduledStep(int $actionId): void;

    public function cancelWorkflowScheduledActions(int $workflowId): void;

    public function isScheduled(string $actionUIDHash): bool;
}
