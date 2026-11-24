<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface ExecutionContextRegistryInterface
{
    /**
     * Gets or creates a workflow execution context for the specified workflow execution
     *
     * @param string $workflowExecutionId The unique identifier for the workflow execution
     * @return ExecutionContextInterface The workflow execution context instance
     */
    public function getExecutionContext(string $executionId): ExecutionContextInterface;

    /**
     * Removes a workflow execution context from the registry
     *
     * @param string $executionId The execution ID
     * @return void
     */
    public function removeExecutionContext(string $executionId): void;
}
