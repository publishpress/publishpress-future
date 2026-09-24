<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use MultipleAuthors\Classes\Utils;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\SetPostAuthors;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;

class SetPostAuthorsRunner implements StepRunnerInterface
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
        return SetPostAuthors::getNodeTypeName();
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

                if (! class_exists(Utils::class)) {
                    $this->logger->debugWithArgs('PublishPress Authors not active, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                $authors = $this->resolveAuthors($this->resolveExpressionField($nodeSettings, 'authors'));

                if (empty($authors)) {
                    $this->logger->debugWithArgs(
                        'No authors resolved, skipping to avoid clearing authors | Slug: %1$s',
                        $nodeSlug
                    );
                    return;
                }

                Utils::set_post_authors($postId, $authors);

                $this->logger->debugWithArgs(
                    'Post authors set (%1$d authors) | Post ID: %2$s | Slug: %3$s',
                    count($authors),
                    $postId,
                    $nodeSlug
                );
            },
            $postId,
            $nodeSettings
        );
    }
}
