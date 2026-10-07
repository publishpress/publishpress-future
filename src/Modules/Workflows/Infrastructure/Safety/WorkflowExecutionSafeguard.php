<?php

namespace PublishPress\Future\Modules\Workflows\Infrastructure\Safety;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowExecutionSafeguardInterface;
use PublishPress\Future\Modules\Workflows\TransientsAbstract;

class WorkflowExecutionSafeguard implements WorkflowExecutionSafeguardInterface
{
    /**
     * @var HookableInterface
     */
    private HookableInterface $hooks;

    /**
     * @var array
     */
    private array $runningNodes = [];

    /**
     * Per-instance trigger execution cache. Shared across request via DI singleton.
     *
     * @var array
     */
    private array $triggerExecutionCache = [];

    public function __construct(
        HookableInterface $hooks
    ) {
        $this->hooks = $hooks;
    }

    public function detectInfiniteLoop(
        ?ExecutionContextInterface $executionContext,
        array $step,
        string $uniqueId = ''
    ): bool {
        $stepId = $step['node']['id'];
        $workflowId = $executionContext->getVariable('global.workflow.id');
        $executionKey = sprintf('%s-%s-%s', $workflowId, $stepId, $uniqueId);

        $infiniteLoopDetected = in_array($executionKey, $this->runningNodes);
        $this->runningNodes[] = $executionKey;

        /**
         * Prevents infinite loops by tracking workflow executions that modify posts,
         * ensuring the same workflow doesn't re-trigger for the same or duplicated posts
         * within a single execution cycle
         */
        if (! is_null($executionContext) && ! $infiniteLoopDetected) {
            $triggerExecutionCacheKey = $this->generateUniqueExecutionIdentifier([
                $executionContext->getVariable('global.engine_execution_id'),
                $executionContext->getVariable('global.workflow.execution_id'),
                $executionContext->getVariable('global.trigger.id'),
                $uniqueId,
            ]);

            if (array_key_exists($triggerExecutionCacheKey, $this->triggerExecutionCache)) {
                return true;
            }

            $this->triggerExecutionCache[$triggerExecutionCacheKey] = time();
        }

        return $infiniteLoopDetected;
    }

    /**
     * Check if a step was recently processed within the threshold
     *
     * Requires the class have a logger and stepProcessor property.
     *
     * @param string $uniqueId
     * @return bool True if the step should be skipped (was recently processed)
     */
    public function preventDuplicateExecution(string $uniqueId): bool
    {
        $duplicatePreventionThreshold = $this->getDuplicatePreventionThreshold();
        $currentTimestamp = microtime(true);

        /**
         * Transients are probably not the most performant way to do this, but we need something
         * that persists across requests. Block editor or third party plugins are firing save_post
         * actions multiple times.
         */
        $lastProcessedTime = get_transient(TransientsAbstract::TRANSIENT_DUPLICATE_PREVENTION . $uniqueId);

        if ($lastProcessedTime) {
            return true;
        }

        set_transient(
            TransientsAbstract::TRANSIENT_DUPLICATE_PREVENTION . $uniqueId,
            $currentTimestamp,
            $duplicatePreventionThreshold
        );

        return false;
    }

    /**
     * Get the duplicate prevention threshold in seconds
     *
     * This defines how long we should wait before allowing the same step to be processed again
     *
     * @return int Threshold in seconds
     */
    protected function getDuplicatePreventionThreshold(): int
    {
        return $this->hooks->applyFilters(
            HooksAbstract::FILTER_DUPLICATE_PREVENTION_THRESHOLD,
            2
        );
    }

    public function generateUniqueExecutionIdentifier(array $elements): string
    {
        return md5(implode('-', $elements));
    }
}
