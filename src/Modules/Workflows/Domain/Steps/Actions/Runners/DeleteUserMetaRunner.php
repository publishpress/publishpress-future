<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\DeleteUserMeta;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;

class DeleteUserMetaRunner implements StepRunnerInterface
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

    public static function getNodeTypeName(): string
    {
        return DeleteUserMeta::getNodeTypeName();
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

                delete_user_meta($userId, $metaKey);

                $this->logger->debugWithArgs(
                    'User meta %1$s deleted | User ID: %2$s | Slug: %3$s',
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
