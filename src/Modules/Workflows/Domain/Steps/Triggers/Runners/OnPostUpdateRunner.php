<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\PostResolver;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\InputValidatorsInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\TriggerRunnerInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\IntegerResolver;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostUpdate;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\PostCacheInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowExecutionSafeguardInterface;

class OnPostUpdateRunner implements TriggerRunnerInterface
{
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
     * @var ExecutionContextInterface
     */
    private $executionContext;

    /**
     * @var string
     */
    private $stepSlug;

    public function __construct(
        HookableInterface $hooks,
        StepProcessorInterface $stepProcessor,
        InputValidatorsInterface $postQueryValidator,
        LoggerInterface $logger,
        \Closure $expirablePostModelFactory,
        PostCacheInterface $postCache,
        WorkflowExecutionSafeguardInterface $workflowExecutionSafeguard,
        ExecutionContextInterface $executionContext
    ) {
        $this->hooks = $hooks;
        $this->stepProcessor = $stepProcessor;
        $this->postQueryValidator = $postQueryValidator;
        $this->executionContext = $executionContext;
        $this->logger = $logger;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->postCache = $postCache;
        $this->executionSafeguard = $workflowExecutionSafeguard;
    }

    public static function getNodeTypeName(): string
    {
        return OnPostUpdate::getNodeTypeName();
    }

    public function setup(int $workflowId, array $step): void
    {
        $this->step = $step;
        $this->stepSlug = $this->stepProcessor->getSlugFromStep($this->step);
        $this->workflowId = $workflowId;

        $this->postCache->setup();

        $this->hooks->addAction(
            HooksAbstract::ACTION_AFTER_INSERT_POST,
            [$this, 'onAfterInsertPostCallback'],
            999,
            3
        );
    }

    /**
     * Fires when the post is saved, after the metadata is saved.
     *
     * @param int $postId
     * @param \WP_Post $post
     * @param bool $update
     * @return void
     */
    public function onAfterInsertPostCallback($postId, $post, $update)
    {
        if ($post->post_type === 'revision') {
            return;
        }

        if (! $update) {
            $this->logger->debugWithArgs(
                'Trigger skipped because post #%d was saved but not updated.',
                $postId
            );

            return;
        }

        $cache = $this->postCache->getCacheForPostId($postId);

        $postBefore = $cache['postBefore'] ?? null;
        $postAfter = $cache['postAfter'] ?? null;

        // Skip only when this is a direct post publishing process (post was never saved before).
        // Do NOT skip when it's a legit update that results in publish (e.g. draft → publish).
        $isDirectPublish = $postBefore
            && $postAfter
            && $postAfter->post_status === 'publish'
            && in_array($postBefore->post_status, ['new', 'auto-draft'], true);

        if ($isDirectPublish) {
            $this->logger->debugWithArgs(
                'Trigger skipped: Direct publish (from "%s" to "publish") for post #%d, not a post update. '
                . 'Post was never saved before; OnPostUpdate requires a genuine update.',
                $postBefore->post_status,
                $postId
            );

            return;
        }

        $this->executionContext->setVariable($this->stepSlug, [
            'postBefore' => new PostResolver(
                $postBefore,
                $this->hooks,
                $cache['permalinkBefore'],
                $this->expirablePostModelFactory
            ),
            'postAfter' => new PostResolver(
                $postAfter,
                $this->hooks,
                $cache['permalinkAfter'],
                $this->expirablePostModelFactory
            ),
            'postId' => new IntegerResolver($postId),
        ]);

        $this->executionContext->setVariable('global.trigger.postId', $postId);

        $postQueryArgs = [
            'post' => $postAfter,
            'node' => $this->step['node'],
        ];

        if (! $this->postQueryValidator->validate($postQueryArgs)) {
            $this->logger->debugWithArgs(
                'Trigger skipped: Post query conditions not met for step %s, post #%d (post_type: %s, post_status: %s).',
                $this->stepSlug,
                $postId,
                $postAfter->post_type ?? 'unknown',
                $postAfter->post_status ?? 'unknown'
            );

            return false;
        }

        if ($this->shouldAbortExecution($postId)) {
            $this->logger->debugWithArgs(
                'Trigger skipped: Execution should be aborted for step %s and post #%d.',
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
                'Trigger skipped: Save post event ignored via filter for step %s and post #%d.',
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
                'Trigger skipped: Infinite loop detected for step %s and post #%d.',
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
                'Trigger skipped: Duplicate execution detected for step %s and post #%d.',
                $this->stepSlug,
                $postId
            );

            return true;
        }

        return false;
    }

    /**
     * Processes the trigger execution.
     *
     * @param array $step
     * @param int $postId
     * @return void
     */
    public function processTriggerExecution(array $step, int $postId): void
    {
        $this->stepProcessor->triggerCallbackIsRunning();

        $this->logger->debugWithArgs('Trigger executed: %s for post #%d.', $this->stepSlug, $postId);

        $this->hooks->doAction(
            HooksAbstract::ACTION_WORKFLOW_TRIGGER_EXECUTED,
            $this->workflowId,
            $this->step
        );

        $this->stepProcessor->runNextSteps($this->step);
    }
}
