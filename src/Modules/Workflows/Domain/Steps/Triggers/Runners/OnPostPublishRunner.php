<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\PostResolver;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\InputValidatorsInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\TriggerRunnerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\IntegerResolver;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostPublish;
use PublishPress\Future\Modules\Workflows\Interfaces\PostCacheInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowExecutionSafeguardInterface;

class OnPostPublishRunner implements TriggerRunnerInterface
{
    /**
     * Transient expiration time in seconds.
     *
     * @var int
     */
    private const TRANSIENT_EXPIRATION = 60;

    /**
     * Transient key for post published. Format: pp_future_post_published_{post_id}_{workflow_id}.
     *
     * @var string
     */
    private const POST_PUBLISHED_TRANSIENT_KEY = 'pp_future_post_published_%d_%d';

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var array
     */
    private $step;

    /**
     * @var StepProcessorInterface
     */
    private $stepProcessor;

    /**
     * @var InputValidatorsInterface
     */
    private $postQueryValidator;

    /**
     * @var int
     */
    private $workflowId;

    /**
     * @var ExecutionContextInterface
     */
    private $executionContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    /**
     * @var PostCacheInterface
     */
    private $postCache;

    /**
     * @var WorkflowExecutionSafeguardInterface
     */
    private $executionSafeguard;

    /**
     * @var string
     */
    private $stepSlug;


    public function __construct(
        HookableInterface $hooks,
        StepProcessorInterface $stepProcessor,
        InputValidatorsInterface $postQueryValidator,
        ExecutionContextInterface $executionContext,
        LoggerInterface $logger,
        \Closure $expirablePostModelFactory,
        PostCacheInterface $postCache,
        WorkflowExecutionSafeguardInterface $executionSafeguard
    ) {
        $this->hooks = $hooks;
        $this->stepProcessor = $stepProcessor;
        $this->postQueryValidator = $postQueryValidator;
        $this->executionContext = $executionContext;
        $this->logger = $logger;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->postCache = $postCache;
        $this->executionSafeguard = $executionSafeguard;
    }

    public static function getNodeTypeName(): string
    {
        return OnPostPublish::getNodeTypeName();
    }

    public function setup(int $workflowId, array $step): void
    {
        $this->step = $step;
        $this->stepSlug = $this->stepProcessor->getSlugFromStep($this->step);
        $this->workflowId = $workflowId;

        $this->postCache->setup();

        $this->hooks->addAction(HooksAbstract::ACTION_TRANSITION_POST_STATUS, [$this, 'onTransitionPostStatus'], 15, 3);

        $this->hooks->addAction(
            HooksAbstract::ACTION_AFTER_INSERT_POST,
            [$this, 'onAfterInsertPostCallback'],
            20,
            3
        );
    }

    /**
     * Fires when the post status is transitioned. If the post is being published,
     * we set a transient to signal that for the next trigger callback.
     *
     * @param string $newStatus
     * @param string $oldStatus
     * @param \WP_Post $post
     * @return void
     */
    public function onTransitionPostStatus($newStatus, $oldStatus, $post)
    {
        if ($newStatus !== 'publish' || $oldStatus === 'publish') {
            $this->logger->debugWithArgs(
                'Trigger skipped: Post #%d was not published or is not being published.',
                $post->ID
            );

            return;
        }

        $this->enableFlag(self::POST_PUBLISHED_TRANSIENT_KEY, $post->ID);
    }

    public function onAfterInsertPostCallback($postId, $post, $update)
    {
        if ($post->post_type === 'revision') {
            return;
        }

        // Do we have the post published flag?
        if (! $this->hasFlag(self::POST_PUBLISHED_TRANSIENT_KEY, $postId)) {
            $this->logger->debugWithArgs(
                'Trigger skipped because post #%d was not published. The flag is not set.',
                $postId
            );

            return false;
        }

        $this->disableFlag(self::POST_PUBLISHED_TRANSIENT_KEY, $postId);

        $postCache = $this->getPostCacheForPostId($postId);
        $postBefore = $postCache['postBefore'] ?? null;
        $postAfter = $postCache['postAfter'] ?? null;

        $stepSlug = $this->stepProcessor->getSlugFromStep($this->step);

        $this->executionContext->setVariable($stepSlug, [
            'postBefore' => new PostResolver(
                $postBefore,
                $this->hooks,
                $postCache['permalinkBefore'] ?? '',
                $this->expirablePostModelFactory
            ),
            'postAfter' => new PostResolver(
                $postAfter,
                $this->hooks,
                $postCache['permalinkAfter'] ?? '',
                $this->expirablePostModelFactory
            ),
            'postId' => new IntegerResolver($postId),
        ]);

        $this->executionContext->setVariable('global.trigger.postId', $postId);

        $postQueryArgs = [
            'post' => $postAfter,
            'node' => $this->stepProcessor->getNodeFromStep($this->step),
        ];

        if (! $this->postQueryValidator->validate($postQueryArgs)) {
            $this->logger->debugWithArgs(
                'Trigger skipped: Post query conditions not met for step "%s" and post #%d.',
                $this->stepSlug,
                $postId
            );

            return false;
        }

        if ($this->shouldAbortExecution($postId)) {
            $this->logger->debugWithArgs(
                'Trigger skipped: Execution should be aborted for step "%s" and post #%d.',
                $this->stepSlug,
                $postId
            );

            return;
        }

        $this->stepProcessor->executeSafelyWithErrorHandling(
            $this->step,
            [$this, 'processTriggerExecution'],
            $postId
        );
    }

    /**
     * @param int $postId
     */
    private function shouldAbortExecution($postId): bool
    {
        if (
            $this->hooks->applyFilters(
                HooksAbstract::FILTER_IGNORE_SAVE_POST_EVENT,
                false,
                self::getNodeTypeName(),
                $this->step
            )
        ) {
            $this->logger->debugWithArgs(
                'Ignored save post event detected for step "%s" and post #%d.',
                $this->stepSlug,
                $postId
            );

            return true;
        }

        if (
            $this->executionSafeguard->detectInfiniteLoop(
                $this->executionContext,
                $this->step,
                $postId
            )
        ) {
            $this->logger->debugWithArgs(
                'Infinite loop detected for step "%s" and post #%d.',
                $this->stepSlug,
                $postId
            );

            return true;
        }

        $uniqueId = $this->executionSafeguard->generateUniqueExecutionIdentifier([
            get_current_user_id(),
            $this->workflowId,
            $this->step['node']['id'],
            $postId,
        ]);

        if ($this->executionSafeguard->preventDuplicateExecution($uniqueId)) {
            $this->logger->debugWithArgs(
                'Duplicate execution detected for step "%s" and post #%d.',
                $this->stepSlug,
                $postId
            );

            return true;
        }

        return false;
    }

    public function processTriggerExecution($postId)
    {
        $stepSlug = $this->stepProcessor->getSlugFromStep($this->step);

        $this->stepProcessor->triggerCallbackIsRunning();

        $this->logger->debugWithArgs('Trigger fired (%s, Post #%d)', $stepSlug, $postId);

        $this->hooks->doAction(
            HooksAbstract::ACTION_WORKFLOW_TRIGGER_EXECUTED,
            $this->workflowId,
            $this->step
        );

        $this->stepProcessor->runNextSteps($this->step);
    }

    private function getPostCacheForPostId($postId)
    {
        $postCache = $this->postCache->getCacheForPostId($postId);

        if (! $postCache) {
            return null;
        }

        return $postCache;
    }

    private function enableFlag($keyFormat, $postId, $value = true)
    {
        set_transient(
            sprintf($keyFormat, $postId, $this->workflowId),
            $value,
            self::TRANSIENT_EXPIRATION
        );
    }

    private function hasFlag($keyFormat, $postId)
    {
        return get_transient(
            sprintf($keyFormat, $postId, $this->workflowId)
        );
    }

    private function disableFlag($keyFormat, $postId)
    {
        delete_transient(
            sprintf($keyFormat, $postId, $this->workflowId)
        );
    }
}
