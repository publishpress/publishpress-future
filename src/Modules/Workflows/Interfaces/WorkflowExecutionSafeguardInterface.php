<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface WorkflowExecutionSafeguardInterface
{
    public function detectInfiniteLoop(
        ?ExecutionContextInterface $executionContext,
        array $step,
        string $uniqueId = ''
    ): bool;

    public function preventDuplicateExecution(string $uniqueId): bool;

    public function generateUniqueExecutionIdentifier(array $elements): string;
}
