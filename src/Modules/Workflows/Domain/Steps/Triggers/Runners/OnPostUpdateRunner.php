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
    private const REST_UPDATE_TRANSIENT_KEY = 'pp_future_rest_update_';

    private const ACF_EXECUTING_TRANSIENT_KEY = 'pp_future_acf_executing_';

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
        $this->workflowId = $workflowId;

        $this->postCache->setup();

        /*
         * We need to use the save_post action because the post_updated action is triggered too early
         * and some post data (like Future Action data) would not be available yet.
         */
        $this->hooks->addAction(HooksAbstract::ACTION_SAVE_POST, [$this, 'triggerCallback'], 15, 3);

        /*
         * Additionally hook into ACF's save_post action for REST API requests.
         * In the block editor, ACF saves metadata AFTER the initial save_post hook,
         * so we need to trigger the workflow again after ACF has processed the fields.
         * Priority 20 ensures this runs after ACF's own processing (priority 10).
         */
        add_action(HooksAbstract::ACTION_ACF_SAVE_POST, [$this, 'triggerCallbackAfterACF'], 20, 1);
    }

    public function triggerCallbackAfterACF($postId)
    {
        // Check if this post was marked as being saved via REST API
        $transientKey = self::REST_UPDATE_TRANSIENT_KEY . $postId;
        $wasRestSave = get_transient($transientKey);

        // Only trigger for posts that were initially saved via REST API (block editor)
        // WP-CLI and admin saves work fine with the regular save_post hook
        if (!$wasRestSave) {
            return;
        }

        // Clear the transient so we don't trigger again on future saves
        delete_transient($transientKey);

        // Set a marker that ACF callback is executing to prevent the secondary save_post from running
        // This prevents duplicate execution when ACF triggers wp_update_post() after saving metadata
        set_transient(self::ACF_EXECUTING_TRANSIENT_KEY . $postId, true, 10);

        $post = get_post($postId);
        if (!$post) {
            return;
        }

        // Call the main trigger callback with the required parameters
        // The $update parameter is true since ACF callbacks only run on updates
        $this->triggerCallback($postId, $post, true);
    }

    public function triggerCallback($postId, $post, $update)
    {
        // Skip REST API requests in the regular save_post hook.
        // For REST requests (block editor), we'll handle the trigger via the acf/save_post hook
        // to ensure ACF metadata is available. This prevents the workflow from running with incomplete data.
        if ($this->isRestRequest()) {
            // Mark this post as being saved via REST so the ACF callback knows to trigger
            set_transient(self::REST_UPDATE_TRANSIENT_KEY . $postId, true, 60);
            return;
        }

        // Check if ACF callback is currently executing or just executed for this post
        // This prevents duplicate execution when ACF triggers wp_update_post() after saving metadata
        $acfExecutingKey = self::ACF_EXECUTING_TRANSIENT_KEY . $postId;
        $acfIsExecuting = get_transient($acfExecutingKey);

        if ($acfIsExecuting && current_filter() === 'save_post') {
            // Clear the marker after blocking the duplicate
            delete_transient($acfExecutingKey);
            return;
        }

        if (! $update) {
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
            $stepSlug = $this->stepProcessor->getSlugFromStep($this->step);
            $this->logger->debug(
                $this->stepProcessor->prepareLogMessage(
                    'Skipping OnPostUpdate for direct publish (from "%s" to "publish") for step %s - not a legit post update',
                    $postBefore->post_status,
                    $stepSlug
                )
            );

            return;
        }

        $stepSlug = $this->stepProcessor->getSlugFromStep($this->step);

        if ($this->shouldAbortExecution($postId, $stepSlug)) {
            return;
        }

        $this->executionContext->setVariable($stepSlug, [
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
            return false;
        }

        $this->stepProcessor->executeSafelyWithErrorHandling(
            $this->step,
            [$this, 'processTriggerExecution'],
            $postId
        );
    }

    private function shouldAbortExecution($postId, $stepSlug): bool
    {
        if (
            $this->hooks->applyFilters(
                HooksAbstract::FILTER_IGNORE_SAVE_POST_EVENT,
                false,
                self::getNodeTypeName(),
                $this->step
            )
        ) {
            $this->logger->debug(
                $this->stepProcessor->prepareLogMessage(
                    'Ignoring save post event for step %s',
                    $stepSlug
                )
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
            $this->logger->debug(
                $this->stepProcessor->prepareLogMessage(
                    'Infinite loop detected for step %s, skipping',
                    $stepSlug
                )
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
            $this->logger->debug(
                $this->stepProcessor->prepareLogMessage(
                    'Duplicate execution detected for step %s, skipping',
                    $stepSlug
                )
            );

            return true;
        }

        return false;
    }

    public function processTriggerExecution($postId)
    {
        $stepSlug = $this->stepProcessor->getSlugFromStep($this->step);

        $this->stepProcessor->triggerCallbackIsRunning();

        $this->logger->debug(
            $this->stepProcessor->prepareLogMessage(
                'Trigger is running | Slug: %s | Post ID: %d',
                $stepSlug,
                $postId
            )
        );

        $this->hooks->doAction(
            HooksAbstract::ACTION_WORKFLOW_TRIGGER_EXECUTED,
            $this->workflowId,
            $this->step
        );

        $this->stepProcessor->runNextSteps($this->step);
    }

    private function isRestRequest()
    {
        return defined('REST_REQUEST') && REST_REQUEST;
    }
}
