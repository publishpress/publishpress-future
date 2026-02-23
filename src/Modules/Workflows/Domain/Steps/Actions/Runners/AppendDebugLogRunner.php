<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\AppendDebugLog;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;

class AppendDebugLogRunner implements StepRunnerInterface
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
        return AppendDebugLog::getNodeTypeName();
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

                $node = $this->stepProcessor->getNodeFromStep($step);
                $nodeSettings = $this->stepProcessor->getNodeSettings($node);

                $message = $nodeSettings['message'] ?? '';

                if (is_array($message)) {
                    $message = $message['expression'] ?? '';
                }

                $message = $this->executionContext->resolveExpressionsInText($message);
                $message = 'Slug: ' . $nodeSlug . ' | ' . $message;

                $availableLevels = [
                    'debug',
                    'info',
                    'notice',
                    'warning',
                    'error',
                    'critical',
                    'alert',
                ];
                $level = $nodeSettings['level'] ?? 'debug';

                if (! in_array($level, $availableLevels)) {
                    $level = 'debug';
                }

                $this->logger->{$level}($message);

                $this->logger->debugWithArgs('Step done (%s)', $nodeSlug);
            }
        );
    }
}
