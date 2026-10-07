<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\WordPress\Utils\ThirdPartyPluginsUtil;
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

/**
 * Trigger runner for the "On Post Update" workflow step.
 *
 * @since 4.6.0
 */
class OnPostUpdateRunner implements TriggerRunnerInterface
{
    /**
     * Whether an ACFE frontend form submission is in progress.
     *
     * @var bool
     * @since 4.10.4
     */
    private static bool $acfeFormSubmissionInProgress = false;

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
     * @var ExecutionContextInterface
     */
    private $executionContext;

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
     * @param LoggerInterface                     $logger                    Logger instance.
     * @param \Closure                            $expirablePostModelFactory Factory for ExpirablePostModel instances.
     * @param PostCacheInterface                  $postCache                 Cache for post before/after snapshots.
     * @param WorkflowExecutionSafeguardInterface $executionSafeguard        Safeguard against loops and duplicates.
     * @param ExecutionContextInterface           $executionContext          Workflow execution context.
     */
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

    /**
     * Returns the node type name for the On Post Update trigger.
     *
     * @return string
     */
    public static function getNodeTypeName(): string
    {
        return OnPostUpdate::getNodeTypeName();
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

        $this->hooks->addAction(
            HooksAbstract::ACTION_AFTER_INSERT_POST,
            [$this, 'onAfterInsertPostCallback'],
            999,
            3
        );

        if (ThirdPartyPluginsUtil::isAcfActive()) {
            $this->addHooksForAcfEnvironment();
        } else {
            $this->addHooksForNonAcfEnvironment();
        }
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
        if ($post->post_type === 'revision') {
            return;
        }

        if (defined('REST_REQUEST') && REST_REQUEST) {
            return;
        }

        if (self::$acfeFormSubmissionInProgress) {
            $this->logger->debugWithArgs(
                'Trigger deferred: ACFE form submission in progress for post #%d.',
                (int) $postId
            );

            return;
        }

        $this->processUpdate((int) $postId, (bool) $update);
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

        $cache = $this->postCache->getCacheForPostId($postId);
        $update = isset($cache['postBefore']);

        $this->processUpdate((int) $postId, $update);
    }

    /**
     * Callback for non-ACF REST API post saves.
     *
     * @param \WP_Post         $post     The post object.
     * @param \WP_REST_Request $request  The REST request.
     * @param bool             $creating Whether this is a new post.
     * @return void
     */
    public function onRestAfterInsertPostCallback(\WP_Post $post, \WP_REST_Request $request, bool $creating): void
    {
        $cache = $this->postCache->getCacheForPostId($post->ID);
        $update = isset($cache['postBefore']);

        $this->processUpdate($post->ID, $update);
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
     * @param array $args   The action arguments.
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

        $cache = $this->postCache->getCacheForPostId($postId);
        $update = isset($cache['postBefore']);

        $this->processUpdate((int) $postId, $update);

        $this->clearAcfeFormSubmissionFlag();
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
     * Clears the ACFE form submission flag.
     *
     * @return void
     * @since 4.10.4
     */
    private function clearAcfeFormSubmissionFlag(): void
    {
        self::$acfeFormSubmissionInProgress = false;
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
     * Validates update conditions and executes the workflow trigger.
     *
     * @param int  $postId The post ID to process.
     * @param bool $update Whether this is an existing post being updated.
     * @return void
     */
    private function processUpdate(int $postId, bool $update): void
    {
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

            return;
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
     * Executes the trigger callback and advances the workflow.
     *
     * @param array $step   The trigger step configuration.
     * @param int   $postId The post ID that triggered the workflow.
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
