<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

interface StepProcessorInterface
{
    public function setup(
        array $step,
        callable $actionCallback
    ): void;

    public function runNextSteps(array $step, string $branch = 'output'): void;

    public function getNextSteps(array $step, string $branch = 'output'): array;

    public function getNodeFromStep(array $step);

    public function getSlugFromStep(array $step);

    public function getNodeSettings(array $node);

    /**
     * @deprecated Use the logger instead
     */
    public function logError(string $message, int $workflowId, array $step);

    public function triggerCallbackIsRunning(): void;

    /**
     * Prepare a log message for the step processor.
     *
     * @param string $message
     * @param mixed ...$args
     * @return string
     * @deprecated 4.10.0 Use the logger instead
     */
    public function prepareLogMessage(string $message, ...$args): string;

    public function executeSafelyWithErrorHandling(array $step, callable $callback, ...$args): void;
}
