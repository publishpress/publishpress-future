<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Processors;

use Exception;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepPostRelatedProcessorInterface;

class Post implements StepProcessorInterface, StepPostRelatedProcessorInterface
{
    public const LOG_PREFIX = '[WorkflowStepsProcessorsPost:%d]: ';

    /**
     * @var HooksFacade
     */
    private $hooks;

    /**
     * @var StepProcessorInterface
     */
    private $generalProcessor;

    /**
     * @var ExecutionContextInterface
     */
    private $executionContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $workflowId;

    public function __construct(
        HooksFacade $hooks,
        StepProcessorInterface $generalProcessor,
        LoggerInterface $logger,
        ExecutionContextInterface $executionContext
    ) {
        $this->hooks = $hooks;
        $this->generalProcessor = $generalProcessor;
        $this->executionContext = $executionContext;
        $this->logger = $logger;
        $this->workflowId = $executionContext->getVariable('global.workflow.id');
    }

    private function getLogPrefix(): string
    {
        return sprintf(self::LOG_PREFIX, $this->workflowId);
    }

    public function setup(array $step, callable $setupCallback): void
    {
        $node = $this->getNodeFromStep($step);
        $nodeSettings = $this->getNodeSettings($node);

        if (! isset($nodeSettings['post'])) {
            $this->logger->errorWithArgs(
                $this->getLogPrefix() . 'The "post" variable is not set in the node settings for step %s',
                $step['node']['data']['slug']
            );

            throw new Exception('The "post" variable is not set in the node settings');
        }

        if (! isset($nodeSettings['post']['variable'])) {
            $this->logger->errorWithArgs(
                $this->getLogPrefix() . 'The "post.variable" variable is not set in the node settings for step %s',
                $step['node']['data']['slug']
            );

            throw new Exception('The "post.variable" variable is not set in the node settings');
        }

        // We look for the "post" variable in the node settings
        $posts = $this->executionContext->getVariable($nodeSettings['post']['variable']);

        if (empty($posts)) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Step %s didn\'t find any posts, skipping',
                $step['node']['data']['slug']
            );

            return;
        }

        if (! is_array($posts)) {
            $posts = [$posts];
        }

        foreach ($posts as $post) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Processing post %s on step %s',
                $post,
                $step['node']['data']['slug']
            );

            if (is_array($post)) {
                if (isset($post['post_id'])) {
                    $postId = $post['post_id'];
                } elseif (isset($post['ID'])) {
                    $postId = $post['ID'];
                }
            } elseif (is_object($post) && isset($post->ID)) {
                $postId = $post->ID;
            } else {
                $postId = intval($post);
            }

            call_user_func($setupCallback, $postId, $nodeSettings, $step);
        }

        $this->runNextSteps($step);
    }

    public function runNextSteps(array $step, string $branch = 'output'): void
    {
        $this->generalProcessor->runNextSteps($step, $branch);
    }

    public function getNextSteps(array $step, string $branch = 'output'): array
    {
        return $this->generalProcessor->getNextSteps($step, $branch);
    }

    public function getNodeFromStep(array $step)
    {
        return $this->generalProcessor->getNodeFromStep($step);
    }

    public function getSlugFromStep(array $step)
    {
        return $this->generalProcessor->getSlugFromStep($step);
    }

    public function getNodeSettings(array $node)
    {
        return $this->generalProcessor->getNodeSettings($node);
    }

    /**
     * @deprecated 4.10.0 Use the logger instead
     */
    public function logError(string $message, int $workflowId, array $step)
    {
        $this->addErrorLogMessage($message);
    }

    public function triggerCallbackIsRunning(): void
    {
        $this->generalProcessor->triggerCallbackIsRunning();
    }

    /**
     * @deprecated 4.10.0 Use the logger instead
     */
    public function prepareLogMessage(string $message, ...$args): string
    {
        return $this->generalProcessor->prepareLogMessage($message, ...$args);
    }

    public function executeSafelyWithErrorHandling(array $step, callable $callback, ...$args): void
    {
        $this->generalProcessor->executeSafelyWithErrorHandling($step, $callback, ...$args);
    }

    /**
     * @deprecated 4.10.0 Use the logger instead
     */
    private function addDebugLogMessage(string $message, ...$args): void
    {
        $this->logger->debugWithArgs($message, ...$args);
    }

    /**
     * @deprecated 4.10.0 Use the logger instead
     */
    private function addErrorLogMessage(string $message, ...$args): void
    {
        $this->logger->errorWithArgs($message, ...$args);
    }

    public function setPostIdOnTriggerGlobalVariable(int $postId): void
    {
        // Store the postID that triggered the workflow in the global variables so
        // we can trace it back to the post.
        $globalVariables = $this->executionContext->getVariable('global');
        $triggerVariable = $globalVariables['trigger'];
        $triggerVariable->postId = $postId;
    }
}
