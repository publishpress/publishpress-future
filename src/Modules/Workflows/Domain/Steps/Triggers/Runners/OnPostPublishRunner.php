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
     * Transient key for going to ACF flow. Format: pp_future_post_published_acf_flow_{post_id}_{workflow_id}.
     *
     * @var string
     */
    private const ACF_FLOW_TRANSIENT_KEY = 'pp_future_post_published_acf_flow_%d_%d';

    /**
     * Transient key for going to block editor flow. Format: pp_future_post_published_block_editor_flow_{post_id}_{workflow_id}.
     *
     * @var string
     */
    private const BLOCK_EDITOR_FLOW_TRANSIENT_KEY = 'pp_future_post_published_block_editor_flow_%d_%d';

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

        $this->hooks->addAction(HooksAbstract::ACTION_TRANSITION_POST_STATUS, [$this, 'onTransitionPostStatus'], 15, 3);

        /*
         * Run at priority 999 so post metadata (and Future Action data) is available.
         * Earlier priorities can run before meta is saved.
         */
        $this->hooks->addAction(HooksAbstract::ACTION_SAVE_POST, [$this, 'onSavePost'], 999, 3);

        /*
         * Run again after ACF has saved the post and meta, so metadata is available.
         */
        $this->hooks->addAction(HooksAbstract::ACTION_ACF_SAVE_POST, [$this, 'onAcfSavePost'], 999);

        /*
         * Run when the post is created via REST API.
         */
        $postTypes = get_post_types(
            [
                'public' => true,
                'show_in_rest' => true,
            ]
        );
        foreach ($postTypes as $postType) {
            $this->hooks->addAction(
                sprintf(HooksAbstract::ACTION_REST_AFTER_INSERT_POST_TYPE, $postType),
                [$this, 'onRestInsertPostType'],
                999,
                2
            );
        }
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
            return;
        }

        $this->enableFlag(self::POST_PUBLISHED_TRANSIENT_KEY, $post->ID);
    }

    /**
     * Fires when the post is saved. If the post is being published,
     * we trigger the callback. If block editor is used and ACF is used,
     * we bypass the regular trigger callback and trigger it from the acf/save_post hook.
     *
     * @param mixed $postId
     * @param mixed $post
     * @param mixed $update
     * @return null|false
     */
    public function onSavePost($postId, $post, $update)
    {
        if (
            $this->hooks->applyFilters(
                HooksAbstract::FILTER_IGNORE_SAVE_POST_EVENT,
                false,
                self::getNodeTypeName(),
                $this->step
            )
        ) {
            return false;
        }

        // Do we have the post published flag?
        if (! $this->hasFlag(self::POST_PUBLISHED_TRANSIENT_KEY, $postId)) {
            return false;
        }

        /*
         * REST (block editor) with ACF: if ACF is active for this post type,
         * block this execution. ACF will send another request to save metadata,
         * and the onAcfSavePost hook will handle the trigger with metadata available.
         */
        if ($this->isRestRequest() && $this->isAcfActiveForPostType($post->post_type)) {
            $this->enableFlag(self::ACF_FLOW_TRANSIENT_KEY, $postId);
            return false;
        }

        /*
         * REST (block editor) without ACF: block this execution and handle it in the onRestInsertPostType hook.
         */
        if ($this->isRestRequest()) {
            $this->enableFlag(self::BLOCK_EDITOR_FLOW_TRANSIENT_KEY, $postId);
            return false;
        }

        // Post is being published out of the Block editor. Trigger the callback.
        $this->disableFlag(self::POST_PUBLISHED_TRANSIENT_KEY, $postId);
        $this->triggerCallback($postId);

        return true;
    }

    public function onRestInsertPostType($post, $request)
    {
        if (! $this->hasFlag(self::POST_PUBLISHED_TRANSIENT_KEY, $post->ID)) {
            return;
        }

        if (! $this->hasFlag(self::BLOCK_EDITOR_FLOW_TRANSIENT_KEY, $post->ID)) {
            return;
        }

        $this->disableFlag(self::POST_PUBLISHED_TRANSIENT_KEY, $post->ID);
        $this->disableFlag(self::BLOCK_EDITOR_FLOW_TRANSIENT_KEY, $post->ID);
        $this->triggerCallback($post->ID);
    }

    /**
     * Runs the OnPostPublish trigger from acf/save_post.
     * Fires after ACF has saved the post and meta, so metadata is available.
     *
     * @param int $postId
     * @return void
     */
    public function onAcfSavePost($postId)
    {
        if (! $this->hasFlag(self::POST_PUBLISHED_TRANSIENT_KEY, $postId)) {
            return;
        }

        $this->disableFlag(self::POST_PUBLISHED_TRANSIENT_KEY, $postId);
        $this->disableFlag(self::ACF_FLOW_TRANSIENT_KEY, $postId);

        // Post is being published, trigger the callback.
        $this->triggerCallback($postId);
    }

    public function triggerCallback($postId)
    {
        $postCache = $this->getPostCacheForPostId($postId);
        $postBefore = $postCache['postBefore'] ?? null;
        $postAfter = $postCache['postAfter'] ?? null;

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
                'Trigger fired (%s, Post #%d)',
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

    private function isAcfActiveForPostType($postType)
    {
        if (! function_exists('acf_get_field_groups')) {
            return false;
        }

        $fieldGroups = call_user_func('acf_get_field_groups', ['post_type' => $postType]);

        return ! empty($fieldGroups);
    }
}
