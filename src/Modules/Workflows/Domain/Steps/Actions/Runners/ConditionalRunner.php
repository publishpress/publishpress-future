<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\Conditional;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;

class ConditionalRunner implements StepRunnerInterface
{
    /**
     * @var StepProcessorInterface
     */
    private $stepProcessor;

    /**
     * @var ExecutionContextInterface
     */
    private $executionContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StepProcessorInterface $stepProcessor,
        ExecutionContextInterface $executionContext,
        LoggerInterface $logger
    ) {
        $this->stepProcessor = $stepProcessor;
        $this->executionContext = $executionContext;
        $this->logger = $logger;
    }

    public static function getNodeTypeName(): string
    {
        return Conditional::getNodeTypeName();
    }

    public function setup(array $step): void
    {
        $this->stepProcessor->setup($step, [$this, 'setupCallback']);
    }

    public function setupCallback(array $step)
    {
        $this->stepProcessor->executeSafelyWithErrorHandling(
            $step,
            function ($step) {
                $nodeSlug = $this->stepProcessor->getSlugFromStep($step);

                // Convert the "true" (default one) to a "next" step.
                // A real conditional split is only handled in the Pro version.
                $step['next']['output'] = $step['next']['true'] ?? [];
                unset($step['next']['true']);
                unset($step['next']['false']);

                $this->executionContext->setVariable($nodeSlug, [
                    'branch' => 'true',
                ]);

                $this->logger->debugWithArgs('Step %1$s is a Pro feature, following to the true branch', $nodeSlug);

                $this->stepProcessor->runNextSteps($step);
            }
        );
    }
}
