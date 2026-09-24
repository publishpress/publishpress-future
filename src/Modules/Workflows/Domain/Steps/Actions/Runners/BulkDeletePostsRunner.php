<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\BulkDeletePosts;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;

class BulkDeletePostsRunner implements StepRunnerInterface
{
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
        StepProcessorInterface $stepProcessor,
        \Closure $expirablePostModelFactory,
        LoggerInterface $logger
    ) {
        $this->stepProcessor = $stepProcessor;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->logger = $logger;
    }

    public static function getNodeTypeName(): string
    {
        return BulkDeletePosts::getNodeTypeName();
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
                $nodeSlug = $this->stepProcessor->getSlugFromStep($step);

                $mode = $nodeSettings['mode'] ?? 'trash';
                if (is_array($mode)) {
                    $mode = $mode['value'] ?? 'trash';
                }

                $postModel = call_user_func($this->expirablePostModelFactory, $postId);

                if ($mode === 'delete') {
                    $postModel->delete(true);
                    $this->logger->debugWithArgs(
                        'Post permanently deleted | Post ID: %1$s | Slug: %2$s',
                        $postId,
                        $nodeSlug
                    );
                } else {
                    $postModel->setPostStatus('trash');
                    $this->logger->debugWithArgs(
                        'Post moved to trash | Post ID: %1$s | Slug: %2$s',
                        $postId,
                        $nodeSlug
                    );
                }
            },
            $postId,
            $nodeSettings
        );
    }
}
