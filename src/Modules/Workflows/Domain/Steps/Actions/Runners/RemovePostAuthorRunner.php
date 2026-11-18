<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use MultipleAuthors\Classes\Utils;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\RemovePostAuthor;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;

class RemovePostAuthorRunner implements StepRunnerInterface
{
    use AuthorsResolverTrait;

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
        return RemovePostAuthor::getNodeTypeName();
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

                if (! class_exists(Utils::class) || ! function_exists('get_post_authors')) {
                    $this->logger->debugWithArgs('PublishPress Authors not active, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                $toRemove = $this->resolveAuthors($this->resolveExpressionField($nodeSettings, 'authors'));
                if (empty($toRemove)) {
                    $this->logger->debugWithArgs('No authors resolved, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                $removeIds = [];
                foreach ($toRemove as $author) {
                    $removeIds[(int) $author->term_id] = true;
                }

                $remaining = [];
                foreach (get_post_authors($postId) as $author) {
                    if (! empty($author->term_id) && ! isset($removeIds[(int) $author->term_id])) {
                        $remaining[] = $author;
                    }
                }

                Utils::set_post_authors($postId, $remaining);

                $this->logger->debugWithArgs(
                    'Removed %1$d author(s) from post | Post ID: %2$s | Slug: %3$s',
                    count($toRemove),
                    $postId,
                    $nodeSlug
                );
            },
            $postId,
            $nodeSettings
        );
    }
}
