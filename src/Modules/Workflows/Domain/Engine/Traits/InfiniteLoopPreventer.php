<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\Traits;

\defined('ABSPATH') || exit;

/**
 * @deprecated 4.4.0 Use the WorkflowExecutionSafeguardInterface instead
 */
trait InfiniteLoopPreventer
{
    /**
     * @var array
     */
    protected $isRunningOnNodes = [];

    /**
     * @deprecated 4.3.2 Use the method isInfiniteLoopDetected instead
     */
    protected function isInfinityLoopDetected(int $workflowId, array $step): bool
    {
        _deprecated_function(__METHOD__, '4.3.2', 'isInfiniteLoopDetected');

        return $this->isInfiniteLoopDetected($workflowId, $step);
    }

    /**
     * @deprecated 4.4.0 Use the interface WorkflowExecutionSafeguardInterface instead
     */
    protected function isInfiniteLoopDetected(int $workflowId, array $step, string $uniqueId = ''): bool
    {
        $stepId = $step['node']['id'];

        $infiniteLoopIndex = sprintf('%s-%s-%s', $workflowId, $stepId, $uniqueId);

        $isAlreadyRunning = in_array($infiniteLoopIndex, $this->isRunningOnNodes);
        $this->isRunningOnNodes[] = $infiniteLoopIndex;

        return $isAlreadyRunning;
    }
}
