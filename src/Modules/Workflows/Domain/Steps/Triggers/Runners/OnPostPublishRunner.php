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
    private const REST_SAVE_TRANSIENT_KEY = 'pp_future_rest_save_';

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
        $this->workflowId = $workflowId;

        $this->postCache->setup();

        /*
         * We need to use the save_post action because the post_updated action is triggered too early
         * and some post data (like Future Action data or post metadata) would not be available yet.
         * This is also fired by `wp_publish_post` function.
         */
        $this->hooks->addAction(HooksAbstract::ACTION_SAVE_POST, [$this, 'triggerCallback'], 15, 3);

        /*
         * Additionally hook into ACF's save_post action for REST API requests.
         * In the block editor, ACF saves metadata AFTER the initial save_post hook,
         * so we need to trigger the workflow again after ACF has processed the fields.
         * Priority 20 ensures this runs after ACF's own processing (priority 10).
         */
        $this->hooks->addAction(HooksAbstract::ACTION_ACF_SAVE_POST, [$this, 'triggerCallbackAfterACF'], 20, 1);
    }

    public function triggerCallbackAfterACF($postId)
    {
        // Check if this post was marked as being saved via REST API
        $transientKey = self::REST_SAVE_TRANSIENT_KEY . $postId;
        $wasRestSave = get_transient($transientKey);

        // Only trigger for posts that were initially saved via REST API (block editor)
        // WP-CLI and admin saves work fine with the regular save_post hook
        if (!$wasRestSave) {
            return;
        }

        // Clear the transient so we don't trigger again on future saves
        delete_transient($transientKey);

        $post = get_post($postId);
        if (!$post || $post->post_status !== 'publish') {
            return;
        }

        // Call the main trigger callback with the required parameters
        // The $update parameter doesn't matter for ACF-triggered calls since we're
        // specifically handling the REST API scenario where metadata is now available
        $this->triggerCallback($postId, $post, true);
    }

    public function triggerCallback($postId, $post, $update)
    {
        if (
            $this->hooks->applyFilters(
                HooksAbstract::FILTER_IGNORE_SAVE_POST_EVENT,
                false,
                self::getNodeTypeName(),
                $this->step
            )
        ) {
            return;
        }

        $postCache = $this->postCache->getCacheForPostId($postId);

        if (! $postCache) {
            return;
        }

        if ($postCache['postAfter']->post_status !== 'publish') {
            return;
        }

        // Do not continue since we are not transitioning to published.
        // EXCEPTION: If called from ACF callback (current_filter is 'acf/save_post'),
        // we want to proceed even if status didn't change because ACF just saved metadata.
        $isCalledFromAcfCallback = current_filter() === HooksAbstract::ACTION_ACF_SAVE_POST;

        if (
            !$isCalledFromAcfCallback
            && $update
            && ! empty($postCache['postBefore']->ID)
            && $postCache['postBefore']->post_status === $postCache['postAfter']->post_status
        ) {
            return;
        }

        // Skip REST API requests in the regular save_post hook.
        // For REST requests (block editor), we'll handle the trigger via the acf/save_post hook
        // to ensure ACF metadata is available. This prevents the workflow from running with incomplete data.
        // IMPORTANT: We only set the transient AFTER confirming this is actually a publish transition.
        if ($this->isRestRequest()) {
            // Mark this post as being saved via REST so the ACF callback knows to trigger
            set_transient(self::REST_SAVE_TRANSIENT_KEY . $postId, true, 60);
            return;
        }

        $stepSlug = $this->stepProcessor->getSlugFromStep($this->step);

        if (
            $this->executionSafeguard->detectInfiniteLoop(
                $this->executionContext,
                $this->step,
                $postId,
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

        $this->executionContext->setVariable($stepSlug, [
            'postBefore' => new PostResolver(
                $postCache['postBefore'],
                $this->hooks,
                $postCache['permalinkBefore'] ?? '',
                $this->expirablePostModelFactory
            ),
            'postAfter' => new PostResolver(
                $postCache['postAfter'],
                $this->hooks,
                $postCache['permalinkAfter'] ?? '',
                $this->expirablePostModelFactory
            ),
            'postId' => new IntegerResolver($postId),
        ]);

        $this->executionContext->setVariable('global.trigger.postId', $postId);

        $postQueryArgs = [
            'post' => $postCache['postAfter'],
            'node' => $this->stepProcessor->getNodeFromStep($this->step),
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
