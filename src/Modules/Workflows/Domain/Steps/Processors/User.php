<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Processors;

use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;

/**
 * Step processor for user-related actions. Mirrors the Post processor: it reads
 * the node's "Target User" field (a `userQuery` field) and resolves it into a
 * list of user IDs, then invokes the runner callback once per user.
 *
 * Resolution rules for the stored `userQuery` value ({userSource, userRole, userId}):
 *  - userSource "custom": explicit `userId` entries plus every user matching one
 *    of the selected `userRole` roles.
 *  - otherwise (default "input"): the user that triggered the workflow, read from
 *    the `global.trigger.userId` execution-context variable set by the user triggers.
 */
class User implements StepProcessorInterface
{
    public const LOG_PREFIX = '[WorkflowStepsProcessorsUser:%d]: ';

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

        $userIds = $this->resolveUserIds($nodeSettings);

        if (empty($userIds)) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Step %s didn\'t find any users, skipping',
                $step['node']['data']['slug']
            );

            return;
        }

        foreach ($userIds as $userId) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Processing user %s on step %s',
                $userId,
                $step['node']['data']['slug']
            );

            call_user_func($setupCallback, $userId, $nodeSettings, $step);
        }

        $this->runNextSteps($step);
    }

    /**
     * @return int[]
     */
    private function resolveUserIds(array $nodeSettings): array
    {
        $userQuery = isset($nodeSettings['userQuery']) && is_array($nodeSettings['userQuery'])
            ? $nodeSettings['userQuery']
            : [];

        $userSource = $userQuery['userSource'] ?? 'input';

        $userIds = [];

        if ($userSource === 'custom') {
            foreach ((array)($userQuery['userId'] ?? []) as $userId) {
                $userId = intval($userId);
                if ($userId > 0) {
                    $userIds[] = $userId;
                }
            }

            $roles = array_filter((array)($userQuery['userRole'] ?? []));
            if (! empty($roles)) {
                $usersByRole = get_users([
                    'role__in' => $roles,
                    'fields' => 'ID',
                ]);

                foreach ($usersByRole as $userId) {
                    $userIds[] = intval($userId);
                }
            }
        } else {
            $triggerUserId = intval($this->executionContext->getVariable('global.trigger.userId'));
            if ($triggerUserId > 0) {
                $userIds[] = $triggerUserId;
            }
        }

        return array_values(array_unique(array_filter($userIds)));
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
        $this->logger->errorWithArgs($message);
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
}
