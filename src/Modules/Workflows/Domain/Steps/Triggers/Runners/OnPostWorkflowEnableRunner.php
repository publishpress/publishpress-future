<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\IntegerResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\PostResolver;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\InputValidatorsInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\TriggerRunnerInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostWorkflowEnable;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepPostRelatedProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowExecutionSafeguardInterface;

class OnPostWorkflowEnableRunner implements TriggerRunnerInterface
{
    public const META_KEY_MANUALLY_TRIGGERED = '_pp_workflow_manually_triggered_';

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var StepPostRelatedProcessorInterface
     */
    private $stepProcessor;

    /**
     * @var array
     */
    private $step;

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
     * @var WorkflowExecutionSafeguardInterface
     */
    private $executionSafeguard;

    /**
     * @var string
     */
    private $stepSlug;

    public function __construct(
        HookableInterface $hooks,
        StepPostRelatedProcessorInterface $stepProcessor,
        InputValidatorsInterface $postQueryValidator,
        ExecutionContextInterface $executionContext,
        LoggerInterface $logger,
        \Closure $expirablePostModelFactory,
        WorkflowExecutionSafeguardInterface $executionSafeguard
    ) {
        $this->hooks = $hooks;
        $this->stepProcessor = $stepProcessor;
        $this->postQueryValidator = $postQueryValidator;
        $this->executionContext = $executionContext;
        $this->logger = $logger;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->executionSafeguard = $executionSafeguard;
    }

    public static function getNodeTypeName(): string
    {
        return OnPostWorkflowEnable::getNodeTypeName();
    }

    public function setup(int $workflowId, array $step): void
    {
        $this->step = $step;
        $this->workflowId = $workflowId;
        $this->stepSlug = $this->stepProcessor->getSlugFromStep($this->step);

        $this->hooks->addAction(HooksAbstract::ACTION_MANUALLY_TRIGGERED_WORKFLOW, [$this, 'triggerCallback'], 10, 2);
    }

    public function triggerCallback($postId, $workflowId)
    {
        $post = get_post($postId);

        $postQueryArgs = [
            'post' => $post,
            'node' => $this->step['node'],
        ];

        // TODO: Do we really need to pass the postID if the post is already being passed?
        $this->executionContext->setVariable(
            $this->stepSlug,
            [
                'postId' => new IntegerResolver($postId),
                'post' => new PostResolver($post, $this->hooks, '', $this->expirablePostModelFactory),
            ]
        );

        $this->executionContext->setVariable('global.trigger.postId', $postId);

        if (! $this->postQueryValidator->validate($postQueryArgs)) {
            $this->logger->debugWithArgs(
                'Trigger skipped: Post query conditions not met for step %s and post #%d.',
                $this->stepSlug,
                $postId
            );

            return false;
        }

        $this->stepProcessor->setPostIdOnTriggerGlobalVariable($postId);

        if ($this->shouldAbortExecution($postId, $workflowId)) {
            $this->logger->debugWithArgs(
                'Trigger skipped: Execution should be aborted for step %s and post #%d.',
                $this->stepSlug,
                $postId
            );
        }

        $this->stepProcessor->executeSafelyWithErrorHandling(
            $this->step,
            [$this, 'processTriggerExecution'],
            $postId
        );
    }

    public function processTriggerExecution($step, $postId)
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

    private function shouldAbortExecution(int $postId, int $workflowId): bool
    {
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

        if ($this->workflowId !== $workflowId) {
            $this->logger->debugWithArgs(
                'Trigger skipped: The workflow ID does not match for step %s and post #%d. Expected workflow ID: %d, but got: %d.',
                $this->stepSlug,
                $postId,
                $this->workflowId,
                $workflowId
            );

            return true;
        }

        return false;
    }
}
