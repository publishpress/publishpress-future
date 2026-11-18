<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\ArrayResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\IntegerResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\PostResolver;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostAuthorsChanged;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\TriggerRunnerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowExecutionSafeguardInterface;

class OnPostAuthorsChangedRunner implements TriggerRunnerInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

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
     * @var WorkflowExecutionSafeguardInterface
     */
    private $executionSafeguard;

    /**
     * @var ExecutionContextInterface
     */
    private $executionContext;

    /**
     * @var array
     */
    private $step;

    /**
     * @var string
     */
    private $stepSlug;

    /**
     * @var int
     */
    private $workflowId;

    public function __construct(
        HookableInterface $hooks,
        StepProcessorInterface $stepProcessor,
        LoggerInterface $logger,
        \Closure $expirablePostModelFactory,
        WorkflowExecutionSafeguardInterface $executionSafeguard,
        ExecutionContextInterface $executionContext
    ) {
        $this->hooks = $hooks;
        $this->stepProcessor = $stepProcessor;
        $this->logger = $logger;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->executionSafeguard = $executionSafeguard;
        $this->executionContext = $executionContext;
    }

    public static function getNodeTypeName(): string
    {
        return OnPostAuthorsChanged::getNodeTypeName();
    }

    public function setup(int $workflowId, array $step): void
    {
        $this->step = $step;
        $this->stepSlug = $this->stepProcessor->getSlugFromStep($this->step);
        $this->workflowId = $workflowId;

        $this->hooks->addAction(
            HooksAbstract::ACTION_PPMA_AUTHORS_SAVED,
            [$this, 'onAuthorsSavedCallback'],
            20,
            1
        );
    }

    public function onAuthorsSavedCallback($postId): void
    {
        $postId = (int) $postId;

        $post = get_post($postId);
        if (! ($post instanceof \WP_Post)) {
            return;
        }

        $names = [];
        $emails = [];
        if (function_exists('get_post_authors')) {
            foreach (get_post_authors($postId) as $author) {
                if (! empty($author->display_name)) {
                    $names[] = $author->display_name;
                }
                $email = isset($author->user_email) ? (string) $author->user_email : '';
                if ($email !== '') {
                    $emails[] = $email;
                }
            }
        }

        $this->executionContext->setVariable($this->stepSlug, [
            'post' => new PostResolver($post, $this->hooks, '', $this->expirablePostModelFactory),
            'postId' => new IntegerResolver($postId),
            'authors' => new ArrayResolver($names),
            'authorEmails' => new ArrayResolver($emails),
        ]);

        $this->executionContext->setVariable('global.trigger.postId', $postId);

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

    private function shouldAbortExecution(int $postId): bool
    {
        $uniqueId = $this->executionSafeguard->generateUniqueExecutionIdentifier([
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
}
