<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\SendRay;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;

class SendRayRunner implements StepRunnerInterface
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
        return SendRay::getNodeTypeName();
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

                if (! function_exists('ray')) {
                    $this->logger->errorWithArgs('Ray is not available. Skipping step %s', $nodeSlug);

                    return;
                }

                $output = null;
                $node = $this->stepProcessor->getNodeFromStep($step);
                $nodeSettings = $this->stepProcessor->getNodeSettings($node);

                $expression = '{{input}}';
                if (isset($nodeSettings['data']['expression'])) {
                    $expression = $nodeSettings['data']['expression'];
                } else {
                    if (isset($nodeSettings['data']['dataToOutput'])) {
                        $expression = '{{' . $nodeSettings['data']['dataToOutput'] . '}}';

                        if ($expression === '{{all-input}}') {
                            $expression = '{{input}}';
                        }
                    }

                    if (isset($nodeSettings['data']['customData'])) {
                        $expression = $nodeSettings['data']['customData'];
                    }
                }

                if ($expression === '{{input}}') {
                    $output = $this->executionContext->getAllVariables();
                    unset($output['global']);
                }

                if ($expression !== '{{input}}') {
                    $output = $this->executionContext->resolveExpressionsInText($expression);
                }

                // phpcs:ignore PublishPressStandards.Debug.DisallowDebugFunctions.FoundRayFunction
                $rayMessage = ray($output);

                $this->logger->debugWithArgs('Ray message sent for step "%s"', $nodeSlug);

                $rayMessage->label($nodeSettings['label'] ?? '');

                if (isset($nodeSettings['color'])) {
                    switch ($nodeSettings['color']) {
                        case 'red':
                            $rayMessage->red();
                            break;
                        case 'green':
                            $rayMessage->green();
                            break;
                        case 'blue':
                            $rayMessage->blue();
                            break;
                        case 'purple':
                            $rayMessage->purple();
                            break;
                        case 'orange':
                            $rayMessage->orange();
                            break;
                        case 'gray':
                            $rayMessage->gray();
                            break;
                    }
                }

                $this->logger->debugWithArgs('Step "%s" done', $nodeSlug);
            }
        );
    }
}
