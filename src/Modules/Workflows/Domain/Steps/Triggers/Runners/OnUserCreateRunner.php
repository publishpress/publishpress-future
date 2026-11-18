<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Runners;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\ArrayResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\IntegerResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\UserResolver;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnUserCreate;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\TriggerRunnerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowExecutionSafeguardInterface;

class OnUserCreateRunner implements TriggerRunnerInterface
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
     * @var ExecutionContextInterface
     */
    private $executionContext;

    /**
     * @var WorkflowExecutionSafeguardInterface
     */
    private $executionSafeguard;

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
        ExecutionContextInterface $executionContext,
        WorkflowExecutionSafeguardInterface $executionSafeguard
    ) {
        $this->hooks = $hooks;
        $this->stepProcessor = $stepProcessor;
        $this->logger = $logger;
        $this->executionContext = $executionContext;
        $this->executionSafeguard = $executionSafeguard;
    }

    public static function getNodeTypeName(): string
    {
        return OnUserCreate::getNodeTypeName();
    }

    public function setup(int $workflowId, array $step): void
    {
        $this->step = $step;
        $this->stepSlug = $this->stepProcessor->getSlugFromStep($this->step);
        $this->workflowId = $workflowId;

        $this->hooks->addAction(
            HooksAbstract::ACTION_USER_REGISTER,
            [$this, 'onUserRegisterCallback'],
            20,
            1
        );
    }

    public function onUserRegisterCallback($userId): void
    {
        $userId = (int) $userId;

        $user = get_userdata($userId);

        if (! ($user instanceof \WP_User)) {
            $this->logger->debugWithArgs(
                'Trigger skipped: User #%d not found for step "%s".',
                $userId,
                $this->stepSlug
            );

            return;
        }

        $userRoles = (array) $user->roles;

        $this->executionContext->setVariable($this->stepSlug, [
            'user' => new UserResolver($user),
            'userId' => new IntegerResolver($userId),
            'roles' => new ArrayResolver($userRoles),
        ]);

        $this->executionContext->setVariable('global.trigger.userId', $userId);

        if (! $this->matchesRoleFilter($userRoles)) {
            $this->logger->debugWithArgs(
                'Trigger skipped: Role conditions not met for step "%s" and user #%d (roles: %s).',
                $this->stepSlug,
                $userId,
                implode(', ', $userRoles)
            );

            return;
        }

        if ($this->shouldAbortExecution($userId)) {
            $this->logger->debugWithArgs(
                'Trigger skipped: Execution should be aborted for step "%s" and user #%d.',
                $this->stepSlug,
                $userId
            );

            return;
        }

        $this->stepProcessor->executeSafelyWithErrorHandling(
            $this->step,
            [$this, 'processTriggerExecution'],
            $userId
        );
    }

    /**
     * A user matches when no role is selected (any role) or one of its roles
     * is in the selected list.
     */
    private function matchesRoleFilter(array $userRoles): bool
    {
        $selectedRoles = $this->getSelectedRoles();

        if (empty($selectedRoles)) {
            return true;
        }

        return count(array_intersect($selectedRoles, $userRoles)) > 0;
    }

    private function getSelectedRoles(): array
    {
        $settings = $this->stepProcessor->getNodeSettings($this->step['node']);

        $selectedRoles = $settings['userQuery']['userRole'] ?? [];

        return is_array($selectedRoles) ? array_values($selectedRoles) : [];
    }

    private function shouldAbortExecution(int $userId): bool
    {
        $uniqueId = $this->executionSafeguard->generateUniqueExecutionIdentifier([
            $this->workflowId,
            $this->step['node']['id'],
            $userId,
        ]);

        if ($this->executionSafeguard->preventDuplicateExecution($uniqueId)) {
            $this->logger->debugWithArgs(
                'Duplicate execution detected for step "%s" and user #%d.',
                $this->stepSlug,
                $userId
            );

            return true;
        }

        return false;
    }

    public function processTriggerExecution($step, $userId)
    {
        $this->stepProcessor->triggerCallbackIsRunning();

        $this->logger->debugWithArgs('Trigger executed: %s for user #%d.', $this->stepSlug, $userId);

        $this->hooks->doAction(
            HooksAbstract::ACTION_WORKFLOW_TRIGGER_EXECUTED,
            $this->workflowId,
            $this->step
        );

        $this->stepProcessor->runNextSteps($this->step);
    }
}
