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

        $this->hooks->addAction(HooksAbstract::ACTION_MANUALLY_TRIGGERED_WORKFLOW, [$this, 'triggerCallback'], 10, 2);
    }

    public function triggerCallback($postId, $workflowId)
    {
        $nodeSlug = $this->stepProcessor->getSlugFromStep($this->step);

        if ($this->shouldAbortExecution($postId, $nodeSlug, $workflowId)) {
            return;
        }

        $this->stepProcessor->executeSafelyWithErrorHandling(
            $this->step,
            [$this, 'fireTheTrigger'],
            $postId
        );
    }

    public function fireTheTrigger($step, $postId)
    {
        $nodeSlug = $this->stepProcessor->getSlugFromStep($step);

        $post = get_post($postId);

        $postQueryArgs = [
            'post' => $post,
            'node' => $this->step['node'],
        ];

        // TODO: Do we really need to pass the postID if the post is already being passed?
        $this->executionContext->setVariable(
            $nodeSlug,
            [
                'postId' => new IntegerResolver($postId),
                'post' => new PostResolver($post, $this->hooks, '', $this->expirablePostModelFactory),
            ]
        );

        $this->executionContext->setVariable('global.trigger.postId', $postId);

        if (! $this->postQueryValidator->validate($postQueryArgs)) {
            return false;
        }

        $this->stepProcessor->setPostIdOnTriggerGlobalVariable($postId);

        $this->logger->debug(
            $this->stepProcessor->prepareLogMessage(
                'Trigger fired (%s, Post #%d)',
                $nodeSlug,
                $postId
            )
        );

        $this->stepProcessor->triggerCallbackIsRunning();

        $this->hooks->doAction(
            HooksAbstract::ACTION_WORKFLOW_TRIGGER_EXECUTED,
            $this->workflowId,
            $this->step
        );

        $this->stepProcessor->runNextSteps($this->step);
    }

    private function shouldAbortExecution(int $postId, string $stepSlug, int $workflowId): bool
    {
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

        if ($this->workflowId !== $workflowId) {
            return true;
        }

        return false;
    }
}
