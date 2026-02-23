<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners;

use PublishPress\Future\Modules\Workflows\Interfaces\TriggerRunnerInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnSchedule;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;

class OnScheduleRunner implements TriggerRunnerInterface
{
    /**
     * @var StepProcessorInterface
     */
    private $stepProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StepProcessorInterface $stepProcessor,
        LoggerInterface $logger
    ) {
        $this->stepProcessor = $stepProcessor;
        $this->logger = $logger;
    }

    /**
     * The default interval in seconds that the setup should be skipped.
     *
     * @var int
     */
    public const DEFAULT_SETUP_INTERVAL = 5;

    public static function getNodeTypeName(): string
    {
        return OnSchedule::getNodeTypeName();
    }

    public function setup(int $workflowId, array $step): void
    {
        $this->stepProcessor->executeSafelyWithErrorHandling(
            $step,
            function ($step) {
                $this->stepProcessor->setup($step, '__return_true');

                $nodeSlug = $this->stepProcessor->getSlugFromStep($step);

                $this->logger->debugWithArgs('Step %1$s is a Pro feature, skipping', $nodeSlug);
            }
        );
    }
}
