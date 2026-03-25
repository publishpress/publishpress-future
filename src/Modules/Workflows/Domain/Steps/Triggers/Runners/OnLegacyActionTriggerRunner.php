<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\PostResolver;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\TriggerRunnerInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\IntegerResolver;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnLegacyActionTrigger;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;

class OnLegacyActionTriggerRunner implements TriggerRunnerInterface
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
     * @var int
     */
    private $workflowId;

    /**
     * @var StepProcessorInterface
     */
    private $stepProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    /**
     * @var ExecutionContextInterface
     */
    private $executionContext;

    public function __construct(
        HookableInterface $hooks,
        StepProcessorInterface $stepProcessor,
        ExecutionContextInterface $executionContext,
        LoggerInterface $logger,
        \Closure $expirablePostModelFactory
    ) {
        $this->hooks = $hooks;
        $this->stepProcessor = $stepProcessor;
        $this->executionContext = $executionContext;
        $this->logger = $logger;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
    }

    public static function getNodeTypeName(): string
    {
        return OnLegacyActionTrigger::getNodeTypeName();
    }

    public function setup(int $workflowId, array $step): void
    {
        $this->step = $step;
        $this->workflowId = $workflowId;

        $this->hooks->addAction(HooksAbstract::ACTION_LEGACY_ACTION, [$this, 'triggerCallback'], 10, 3);
    }

    public function triggerCallback($postId, $post, $args = [])
    {
        // Check if it came from the correct workflow
        if (! isset($args['workflowId']) || (int) $this->workflowId !== (int) $args['workflowId']) {
            return false;
        }

        $this->stepProcessor->executeSafelyWithErrorHandling(
            $this->step,
            function ($step, $postId, $post) {
                $nodeSlug = $this->stepProcessor->getSlugFromStep($step);

                $this->executionContext->setVariable($nodeSlug, [
                    'post' => new PostResolver($post, $this->hooks, '', $this->expirablePostModelFactory),
                    'postId' => new IntegerResolver($postId),
                ]);

                $this->executionContext->setVariable('global.trigger.postId', $postId);

                $this->stepProcessor->triggerCallbackIsRunning();


                $this->logger->debugWithArgs('Trigger fired (%s, Post #%d)', $nodeSlug, $postId);

                $this->hooks->doAction(
                    HooksAbstract::ACTION_WORKFLOW_TRIGGER_EXECUTED,
                    $this->workflowId,
                    $this->step
                );

                $this->stepProcessor->runNextSteps($this->step);
            },
            $postId,
            $post
        );
    }
}
