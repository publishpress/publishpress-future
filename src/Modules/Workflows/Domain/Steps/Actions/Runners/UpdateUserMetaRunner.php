<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\UpdateUserMeta;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;

class UpdateUserMetaRunner implements StepRunnerInterface
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
        return UpdateUserMeta::getNodeTypeName();
    }

    public function setup(array $step): void
    {
        $this->stepProcessor->setup($step, [$this, 'setupCallback']);
    }

    public function setupCallback(int $userId, array $nodeSettings, array $step)
    {
        $this->stepProcessor->executeSafelyWithErrorHandling(
            $step,
            function ($step, $userId, $nodeSettings) {
                $nodeSlug = $this->stepProcessor->getSlugFromStep($step);

                $metaKey = trim((string)($nodeSettings['metaKey'] ?? ''));
                if ($metaKey === '') {
                    $this->logger->debugWithArgs('Meta key is empty, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                $metaValue = $nodeSettings['metaValue'] ?? '';
                if (is_array($metaValue)) {
                    $metaValue = $metaValue['expression'] ?? '';
                }
                $metaValue = $this->executionContext->resolveExpressionsInText((string)$metaValue);

                update_user_meta($userId, $metaKey, $metaValue);

                $this->logger->debugWithArgs(
                    'User meta %1$s updated | User ID: %2$s | Slug: %3$s',
                    $metaKey,
                    $userId,
                    $nodeSlug
                );
            },
            $userId,
            $nodeSettings
        );
    }
}
