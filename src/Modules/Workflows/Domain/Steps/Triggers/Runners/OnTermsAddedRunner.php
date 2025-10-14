<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\IntegerResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\PostResolver;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\InputValidatorsInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\TriggerRunnerInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnTermsAdded;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowExecutionSafeguardInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\PostCacheInterface;

class OnTermsAddedRunner implements TriggerRunnerInterface
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
        $this->logger = $logger;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->postCache = $postCache;
        $this->executionSafeguard = $workflowExecutionSafeguard;
        $this->executionContext = $executionContext;
    }

    public static function getNodeTypeName(): string
    {
        return OnTermsAdded::getNodeTypeName();
    }

    public function setup(int $workflowId, array $step): void
    {
        $this->step = $step;
        $this->workflowId = $workflowId;
        $this->postCache->setup();
        $this->hooks->addAction(HooksAbstract::ACTION_SET_OBJECT_TERMS, [$this, 'triggerCallback'], 20, 6);
    }

    public function triggerCallback($objectId, $terms, $ttIds, $taxonomy, $append, $oldTtIds)
    {
        $post = get_post($objectId);

        if (!$post) {
            return;
        }

        $stepSlug = $this->stepProcessor->getSlugFromStep($this->step);

        // Get added terms from post cache
        $addedTermIds = $this->postCache->getAddedTermsIds($objectId, $taxonomy);

        if (empty($addedTermIds)) {
            // we should only execute this if post term is added
            return;
        }

        if (!$this->matchesTermsFilter($addedTermIds, $taxonomy)) {
            return;
        }

        if ($this->shouldAbortExecution($objectId, $stepSlug)) {
            return;
        }

        // Get cached post and term states for execution context
        $cache = $this->postCache->getCacheForPostId($objectId);

        $postBefore = $cache['postBefore'] ?? $post;
        $postAfter = $cache['postAfter'] ?? $post;

        $this->executionContext->setVariable($stepSlug, [
            'postBefore' => new PostResolver(
                $postBefore,
                $this->hooks,
                $cache['permalinkBefore'] ?? '',
                $this->expirablePostModelFactory,
                $cache['termsBefore'] ?? []
            ),
            'postAfter' => new PostResolver(
                $postAfter,
                $this->hooks,
                $cache['permalinkAfter'] ?? '',
                $this->expirablePostModelFactory,
                $cache['termsAfter'] ?? []
            ),
            'post' => new PostResolver(
                $post,
                $this->hooks,
                '',
                $this->expirablePostModelFactory
            ),
            'postId' => new IntegerResolver($objectId),
        ]);

        $this->executionContext->setVariable('global.trigger.postId', $objectId);

        $postQueryArgs = [
            'post' => $postAfter,
            'node' => $this->step['node'],
        ];

        if (!$this->postQueryValidator->validate($postQueryArgs)) {
            return false;
        }

        $this->stepProcessor->executeSafelyWithErrorHandling(
            $this->step,
            [$this, 'processTriggerExecution'],
            $objectId
        );
    }

    private function matchesTermsFilter($addedTermIds, $taxonomy): bool
    {
        $termsQuery = $this->step['node']['data']['settings']['termsQuery'] ?? [];

        if (empty($termsQuery)) {
            return true;
        }

        $filterTaxonomy = $termsQuery['taxonomy'] ?? '';
        $filterTerms = $termsQuery['terms'] ?? [];

        if (!empty($filterTaxonomy) && $filterTaxonomy !== $taxonomy) {
            return false;
        }

        if (!empty($filterTerms)) {
            return count(array_intersect($addedTermIds, $filterTerms)) > 0;
        }

        return true;
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
                    'Ignoring terms added event for step %s',
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
}
