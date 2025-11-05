<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

use Closure;

interface WorkflowEngineInterface
{
    public function start();

    /**
     * @since 4.6.0
     */
    public function runWorkflows(array $workflowIdsToRun = []);

    public function runWorkflowsFromCache(array $workflowIdsToRun = []);

    public function setCurrentAsyncActionId($actionId);

    public function getCurrentAsyncActionId(): int;

    /**
     * @since 4.4.1
     */
    public function getEngineExecutionId(): string;

    /**
     * @since 4.4.1
     */
    public function prepareExecutionContextForWorkflow(
        string $workflowExecutionId,
        WorkflowModelInterface $workflowModel
    ): void;

    /**
     * @since 4.4.1
     */
    public function prepareExecutionContextForTrigger(
        string $workflowExecutionId,
        array $triggerStep
    ): void;

    /**
     * @since 4.4.1
     */
    public function generateUniqueId(): string;

    /**
     * @since 4.6.0
     */
    public function getExecutionContextRegistry(): ExecutionContextRegistryInterface;
}
