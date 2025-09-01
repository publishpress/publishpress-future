<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\ChangePostStatus;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;

class ChangePostStatusRunner implements StepRunnerInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var StepProcessorInterface
     */
    private $stepProcessor;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        HookableInterface $hooks,
        StepProcessorInterface $stepProcessor,
        \Closure $expirablePostModelFactory,
        LoggerInterface $logger
    ) {
        $this->hooks = $hooks;
        $this->stepProcessor = $stepProcessor;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->logger = $logger;
    }

    public static function getNodeTypeName(): string
    {
        return ChangePostStatus::getNodeTypeName();
    }

    public function setup(array $step): void
    {
        $this->stepProcessor->setup($step, [$this, 'setupCallback']);
    }

    public function setupCallback(int $postId, array $nodeSettings, array $step)
    {
        $this->stepProcessor->executeSafelyWithErrorHandling(
            $step,
            function ($step, $postId, $nodeSettings) {
                $this->hooks->addFilter(HooksAbstract::FILTER_IGNORE_SAVE_POST_EVENT, '__return_true', 10);

                $nodeSlug = $this->stepProcessor->getSlugFromStep($step);

                $postModel = call_user_func($this->expirablePostModelFactory, $postId);

                $oldStatus = $postModel->getPostStatus();
                $newStatus = $nodeSettings['newStatus']['status'];

                if ($oldStatus === $newStatus) {
                    $this->logger->debug(
                        $this->stepProcessor->prepareLogMessage(
                            'Post status is the same, skipping | Post ID: %1$s | Slug: %2$s',
                            $postId,
                            $nodeSlug
                        )
                    );

                    return;
                }

                if ('publish' === $newStatus) {
                    $postModel->publish();

                    $this->logger->debug(
                        $this->stepProcessor->prepareLogMessage(
                            'Post published | Post ID: %1$s | Slug: %2$s',
                            $postId,
                            $nodeSlug
                        )
                    );
                } else {
                    $postModel->setPostStatus($newStatus);

                    $this->logger->debug(
                        $this->stepProcessor->prepareLogMessage(
                            'Post status changed from %1$s to %2$s | Post ID: %3$s | Slug: %4$s',
                            $oldStatus,
                            $newStatus,
                            $postId,
                            $nodeSlug
                        )
                    );
                }

                $this->hooks->removeFilter(HooksAbstract::FILTER_IGNORE_SAVE_POST_EVENT, '__return_true', 10);
            },
            $postId,
            $nodeSettings
        );
    }
}
