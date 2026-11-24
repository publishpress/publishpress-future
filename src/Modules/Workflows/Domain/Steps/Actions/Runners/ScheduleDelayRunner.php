<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\ScheduleDelay;
use PublishPress\Future\Modules\Workflows\Interfaces\AsyncStepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\AsyncStepRunnerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;

\defined('ABSPATH') || exit;

class ScheduleDelayRunner implements AsyncStepRunnerInterface
{
    /**
     * @var AsyncStepProcessorInterface
     */
    private $asyncStepProcessor;

    /**
     * @var ExecutionContextInterface
     */
    private $executionContext;

    public function __construct(
        AsyncStepProcessorInterface $asyncStepProcessor,
        ExecutionContextInterface $executionContext
    ) {
        $this->asyncStepProcessor = $asyncStepProcessor;
        $this->executionContext = $executionContext;
    }

    public static function getNodeTypeName(): string
    {
        return ScheduleDelay::getNodeTypeName();
    }

    public function setup(array $step): void
    {
        $this->asyncStepProcessor->executeSafelyWithErrorHandling(
            $step,
            function ($step) {
                $this->asyncStepProcessor->setup($step, '__return_true');
            }
        );
    }

    /**
     * This method is called when the action is triggered by the scheduler.
     */
    public function actionCallback(array $compactedArgs, array $originalArgs)
    {
        $this->asyncStepProcessor->actionCallback($compactedArgs, $originalArgs);
    }
}
