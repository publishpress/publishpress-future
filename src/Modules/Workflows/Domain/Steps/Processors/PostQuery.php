<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Processors;

use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use WP_Query;

/**
 * Step processor for self-contained bulk post actions. Unlike the Post processor
 * (which resolves a single "post" variable coming from an upstream step), this
 * processor reads the node's own `postQuery` field, resolves it into the full set
 * of matching post IDs, and invokes the runner callback once per post.
 *
 * The `postQuery` value shape ({postSource, postType, postId, postStatus}) is the
 * same one used by the QueryPosts node and the post triggers.
 */
class PostQuery implements StepProcessorInterface
{
    public const LOG_PREFIX = '[WorkflowStepsProcessorsPostQuery:%d]: ';

    /**
     * Hard safety ceiling on the number of posts a single bulk action will touch,
     * so an over-broad query cannot lock up a request. Adjustable via filter.
     */
    public const DEFAULT_MAX_POSTS = 1000;

    /**
     * @var HooksFacade
     */
    private $hooks;

    /**
     * @var StepProcessorInterface
     */
    private $generalProcessor;

    /**
     * @var ExecutionContextInterface
     */
    private $executionContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $workflowId;

    public function __construct(
        HooksFacade $hooks,
        StepProcessorInterface $generalProcessor,
        LoggerInterface $logger,
        ExecutionContextInterface $executionContext
    ) {
        $this->hooks = $hooks;
        $this->generalProcessor = $generalProcessor;
        $this->executionContext = $executionContext;
        $this->logger = $logger;
        $this->workflowId = $executionContext->getVariable('global.workflow.id');
    }

    private function getLogPrefix(): string
    {
        return sprintf(self::LOG_PREFIX, $this->workflowId);
    }

    public function setup(array $step, callable $setupCallback): void
    {
        $node = $this->getNodeFromStep($step);
        $nodeSettings = $this->getNodeSettings($node);

        $postIds = $this->resolvePostIds($nodeSettings);

        if (empty($postIds)) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Step %s didn\'t match any posts, skipping',
                $step['node']['data']['slug']
            );

            return;
        }

        foreach ($postIds as $postId) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Processing post %s on step %s',
                $postId,
                $step['node']['data']['slug']
            );

            call_user_func($setupCallback, $postId, $nodeSettings, $step);
        }

        $this->runNextSteps($step);
    }

    /**
     * @return int[]
     */
    private function resolvePostIds(array $nodeSettings): array
    {
        $query = isset($nodeSettings['postQuery']) && is_array($nodeSettings['postQuery'])
            ? $nodeSettings['postQuery']
            : [];

        $postIds = [];

        foreach ((array)($query['postId'] ?? []) as $postId) {
            $postId = intval($postId);
            if ($postId > 0) {
                $postIds[] = $postId;
            }
        }

        if (empty($postIds)) {
            $postTypes = array_values(array_filter((array)($query['postType'] ?? [])));
            if (empty($postTypes)) {
                $postTypes = ['post'];
            }

            $postStatuses = array_values(array_filter((array)($query['postStatus'] ?? [])));

            $maxPosts = (int) $this->hooks->applyFilters(
                'publishpressfuture_bulk_action_max_posts',
                self::DEFAULT_MAX_POSTS
            );

            $wpQuery = new WP_Query([
                'post_type' => $postTypes,
                'post_status' => ! empty($postStatuses) ? $postStatuses : 'any',
                'posts_per_page' => $maxPosts > 0 ? $maxPosts : -1,
                'fields' => 'ids',
                'no_found_rows' => true,
                'ignore_sticky_posts' => true,
                'suppress_filters' => false,
            ]);

            foreach ($wpQuery->posts as $postId) {
                $postIds[] = intval($postId);
            }
        }

        return array_values(array_unique(array_filter($postIds)));
    }

    public function runNextSteps(array $step, string $branch = 'output'): void
    {
        $this->generalProcessor->runNextSteps($step, $branch);
    }

    public function getNextSteps(array $step, string $branch = 'output'): array
    {
        return $this->generalProcessor->getNextSteps($step, $branch);
    }

    public function getNodeFromStep(array $step)
    {
        return $this->generalProcessor->getNodeFromStep($step);
    }

    public function getSlugFromStep(array $step)
    {
        return $this->generalProcessor->getSlugFromStep($step);
    }

    public function getNodeSettings(array $node)
    {
        return $this->generalProcessor->getNodeSettings($node);
    }

    /**
     * @deprecated 4.10.0 Use the logger instead
     */
    public function logError(string $message, int $workflowId, array $step)
    {
        $this->logger->errorWithArgs($message);
    }

    public function triggerCallbackIsRunning(): void
    {
        $this->generalProcessor->triggerCallbackIsRunning();
    }

    /**
     * @deprecated 4.10.0 Use the logger instead
     */
    public function prepareLogMessage(string $message, ...$args): string
    {
        return $this->generalProcessor->prepareLogMessage($message, ...$args);
    }

    public function executeSafelyWithErrorHandling(array $step, callable $callback, ...$args): void
    {
        $this->generalProcessor->executeSafelyWithErrorHandling($step, $callback, ...$args);
    }
}
