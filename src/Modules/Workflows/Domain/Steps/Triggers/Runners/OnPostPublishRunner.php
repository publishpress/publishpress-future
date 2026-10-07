<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\WordPress\Utils\ThirdPartyPluginsUtil;
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

/**
 * Trigger runner for the "On Post Publish" workflow step.
 *
 * @since 4.6.0
 */
class OnPostPublishRunner implements TriggerRunnerInterface
{
    /**
     * Whether an ACFE frontend form submission is in progress.
     *
     * @var bool
     * @since 4.10.4
     */
    private static bool $acfeFormSubmissionInProgress = false;

    /**
     * Transient TTL in seconds for the publish flag.
     *
     * @var int
     */
    private const TRANSIENT_EXPIRATION = 60;

    /**
     * sprintf format for the per-post, per-workflow publish transient key.
     *
     * @var string
     */
    private const POST_PUBLISHED_TRANSIENT_KEY = 'pp_future_post_published_%d_%d';

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * Workflow step configuration for the trigger node.
     *
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
     * ID of the workflow this runner instance is bound to.
     *
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
     * Factory closure that creates an ExpirablePostModel for a given post ID.
     *
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
     * Slug of the trigger step, used for execution context variable names.
     *
     * @var string
     */
    private $stepSlug;

    /**
     * @param HookableInterface                   $hooks                     WordPress hooks facade.
     * @param StepProcessorInterface              $stepProcessor             Step processor for workflow execution.
     * @param InputValidatorsInterface            $postQueryValidator        Validator for post query conditions.
     * @param ExecutionContextInterface           $executionContext          Workflow execution context.
     * @param LoggerInterface                     $logger                    Logger instance.
     * @param \Closure                            $expirablePostModelFactory Factory for ExpirablePostModel instances.
     * @param PostCacheInterface                  $postCache                 Cache for post before/after snapshots.
     * @param WorkflowExecutionSafeguardInterface $executionSafeguard        Safeguard against loops and duplicates.
     */
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

    /**
     * Returns the node type name for the On Post Publish trigger.
     *
     * @return string
     */
    public static function getNodeTypeName(): string
    {
        return OnPostPublish::getNodeTypeName();
    }

    /**
     * Registers WordPress hooks for this trigger runner.
     *
     * @param int   $workflowId The workflow ID to bind this runner to.
     * @param array $step       The trigger step configuration.
     * @return void
     */
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

        if (ThirdPartyPluginsUtil::isAcfActive()) {
            $this->addHooksForAcfEnvironment();
        } else {
            $this->addHooksForNonAcfEnvironment();
        }
    }

    /**
     * Sets the publish transient flag on status transition.
     *
     * @param string   $newStatus The new post status.
     * @param string   $oldStatus The previous post status.
     * @param \WP_Post $post      The post object.
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

    /**
     * Callback for admin non-REST post saves.
     *
     * @param int      $postId The post ID.
     * @param \WP_Post $post   The post object.
     * @param bool     $update Whether this is an existing post being updated.
     * @return void
     */
    public function onAfterInsertPostCallback($postId, $post, $update)
    {
        if (defined('REST_REQUEST') && REST_REQUEST) {
            return;
        }

        if ($post->post_type === 'revision') {
            return;
        }

        if (self::$acfeFormSubmissionInProgress) {
            $this->logger->debugWithArgs(
                'Trigger deferred: ACFE form submission in progress for post #%d.',
                (int) $postId
            );

            return;
        }

        $this->processPublish((int) $postId);
    }

    /**
     * Callback for ACF REST API post saves.
     *
     * @param int|null $postId The post ID.
     * @return void
     */
    public function onAcfSavePostCallback($postId): void
    {
        if (! defined('REST_REQUEST') || ! REST_REQUEST) {
            return;
        }

        $post = get_post($postId);

        if (! ($post instanceof \WP_Post)) {
            return;
        }

        $this->processPublish((int) $postId);
    }

    /**
     * Sets the ACFE form submission flag before post actions run.
     *
     * @param array $form The form configuration.
     * @return void
     * @since 4.10.4
     */
    public function onAcfeFormSubmitFormStartCallback(array $form): void
    {
        if (is_admin()) {
            return;
        }

        self::$acfeFormSubmissionInProgress = true;
    }

    /**
     * Clears the ACFE form submission flag.
     *
     * @param array $form The form configuration.
     * @return void
     * @since 4.10.4
     */
    public function onAcfeFormSubmitFormEndCallback(array $form): void
    {
        self::$acfeFormSubmissionInProgress = false;
    }

    /**
     * Callback for ACFE Post Action.
     *
     * @param int   $postId The post ID created or updated by the form action.
     * @param array $args   The action arguments, including post_status.
     * @param array $form   The form configuration.
     * @param array $action The action configuration.
     * @return void
     * @since 4.10.4
     */
    public function onAcfeFormSubmitPostCallback(int $postId, array $args, array $form, array $action): void
    {
        if (is_admin()) {
            return;
        }

        $postStatus = $args['post_status'] ?? '';

        if ($postStatus !== 'publish') {
            $post = get_post($postId);

            if (! ($post instanceof \WP_Post) || $post->post_status !== 'publish') {
                return;
            }
        }

        $this->processPublish((int) $postId);

        $this->clearAcfeFormSubmissionFlag();
    }

    /**
     * Callback for non-ACF REST API post saves.
     *
     * @param \WP_Post          $post     The post object.
     * @param \WP_REST_Request  $request  The REST request.
     * @param bool              $creating Whether this is a new post.
     * @return void
     */
    public function onRestAfterInsertPostCallback(\WP_Post $post, \WP_REST_Request $request, bool $creating): void
    {
        $this->processPublish($post->ID);
    }

    /**
     * Returns all registered post types for REST hook registration.
     *
     * @return array<string, string> Post type objects keyed by post type name.
     */
    private function getPostTypes(): array
    {
        return get_post_types();
    }

    /**
     * Validates the publish flag and executes the workflow trigger.
     *
     * @param int $postId The post ID to process.
     * @return void
     */
    private function processPublish(int $postId): void
    {
        if (! $this->hasFlag(self::POST_PUBLISHED_TRANSIENT_KEY, $postId)) {
            $this->logger->debugWithArgs(
                'Trigger skipped because post #%d was not published. The flag is not set.',
                $postId
            );

            return;
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

            return;
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
     * Determines whether trigger execution should be aborted.
     *
     * @param int $postId The post ID being processed.
     * @return bool True if execution should be aborted, false otherwise.
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

    /**
     * Executes the trigger callback and advances the workflow.
     *
     * @param int $postId The post ID that triggered the workflow.
     * @return void
     */
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

    /**
     * Registers hooks for environments where ACF is active.
     *
     * @return void
     */
    private function addHooksForAcfEnvironment(): void
    {
        $this->hooks->addAction(
            HooksAbstract::ACTION_ACF_SAVE_POST,
            [$this, 'onAcfSavePostCallback'],
            20,
            1
        );

        if (ThirdPartyPluginsUtil::isAcfeActive()) {
            $this->hooks->addAction(
                HooksAbstract::ACTION_ACFE_FORM_SUBMIT_FORM,
                [$this, 'onAcfeFormSubmitFormStartCallback'],
                1,
                1
            );

            $this->hooks->addAction(
                HooksAbstract::ACTION_ACFE_FORM_SUBMIT_POST,
                [$this, 'onAcfeFormSubmitPostCallback'],
                20,
                4
            );

            $this->hooks->addAction(
                HooksAbstract::ACTION_ACFE_FORM_SUBMIT_FORM,
                [$this, 'onAcfeFormSubmitFormEndCallback'],
                999,
                1
            );
        }
    }

    /**
     * Registers hooks for environments where ACF is not active.
     *
     * @return void
     */
    private function addHooksForNonAcfEnvironment(): void
    {
        foreach ($this->getPostTypes() as $postType) {
            $this->hooks->addAction(
                sprintf(HooksAbstract::ACTION_REST_AFTER_INSERT_POST_TYPE, $postType),
                [$this, 'onRestAfterInsertPostCallback'],
                20,
                3
            );
        }
    }

    /**
     * Retrieves the post before/after cache entry for a post ID.
     *
     * @param int $postId The post ID.
     * @return array|null Cached post data with before/after snapshots, or null if not available.
     */
    private function getPostCacheForPostId($postId)
    {
        $postCache = $this->postCache->getCacheForPostId($postId);

        if (! $postCache) {
            return null;
        }

        return $postCache;
    }

    /**
     * Sets a transient flag for the given post and workflow.
     *
     * @param string $keyFormat sprintf format for the transient key (expects post ID and workflow ID placeholders).
     * @param int    $postId    The post ID.
     * @param bool   $value     The flag value to store.
     * @return void
     */
    private function enableFlag($keyFormat, $postId, $value = true)
    {
        set_transient(
            sprintf($keyFormat, $postId, $this->workflowId),
            $value,
            self::TRANSIENT_EXPIRATION
        );
    }

    /**
     * Checks whether a transient flag is set for the given post and workflow.
     *
     * @param string $keyFormat sprintf format for the transient key (expects post ID and workflow ID placeholders).
     * @param int    $postId    The post ID.
     * @return mixed The transient value, or false if not set or expired.
     */
    private function hasFlag($keyFormat, $postId)
    {
        return get_transient(
            sprintf($keyFormat, $postId, $this->workflowId)
        );
    }

    /**
     * Removes a transient flag for the given post and workflow.
     *
     * @param string $keyFormat sprintf format for the transient key (expects post ID and workflow ID placeholders).
     * @param int    $postId    The post ID.
     * @return void
     */
    private function disableFlag($keyFormat, $postId)
    {
        delete_transient(
            sprintf($keyFormat, $postId, $this->workflowId)
        );
    }

    /**
     * Clears the ACFE form submission flag.
     *
     * @return void
     * @since 4.10.4
     */
    private function clearAcfeFormSubmissionFlag(): void
    {
        self::$acfeFormSubmissionInProgress = false;
    }
}
