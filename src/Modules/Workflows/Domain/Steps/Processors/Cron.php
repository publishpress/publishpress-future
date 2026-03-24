<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Processors;

use Exception;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Expirator\Interfaces\CronInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\Traits\TimestampCalculator;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\AsyncStepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\CronSchedulesModelInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Models\ScheduledActionModel;
use PublishPress\Future\Modules\Workflows\Models\ScheduledActionsModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowScheduledStepModel;
use Throwable;

class Cron implements AsyncStepProcessorInterface
{
    use TimestampCalculator;

    public const LOG_PREFIX = '[WorkflowStepsProcessorsCron:%d]: ';

    public const DEFAULT_REPEAT_UNTIL_TIMES = PHP_INT_MAX;

    public const WHEN_TO_RUN_NOW = 'now';

    private const DEFAULT_PRIORITY = 10;

    /**
     * @deprecated version 4.0.0
     */
    public const WHEN_TO_RUN_EVENT = 'event';

    public const WHEN_TO_RUN_DATE = 'date';

    public const WHEN_TO_RUN_OFFSET = 'offset';

    public const DATE_SOURCE_CALENDAR = 'calendar';

    public const DATE_SOURCE_EVENT = 'event';

    public const DATE_SOURCE_STEP = 'step';

    public const DATE_SOURCE_CUSTOM = 'custom';

    public const SCHEDULE_RECURRENCE_SINGLE = 'single';

    public const SCHEDULE_RECURRENCE_CUSTOM = 'custom';

    public const REPEAT_UNTIL_DATE = 'date';

    public const REPEAT_UNTIL_TIMES = 'times';

    public const UNSCHEDULE_FUTURE_ACTION_DELAY = 5;

    /**
     * @var HooksFacade
     */
    private $hooks;

    /**
     * @var StepProcessorInterface
     */
    private $generalProcessor;

    /**
     * @var CronInterface
     */
    private $cron;

    /**
     * @var CronSchedulesModelInterface
     */
    private $cronSchedulesModel;

    /**
     * @var string
     */
    private $pluginVersion;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    /**
     * @var string
     */
    private $workflowExecutionId;

    /**
     * @var ExecutionContextInterface
     */
    private $executionContext;

    /**
     * @var int
     */
    private $scheduledActionId;

    /**
     * @var string
     */
    private $stepSlug;

    /**
     * @var int
     */
    private $workflowId;

    /**
     * @var string
     */
    private $actionUID;

    /**
     * @var string
     */
    private $actionUIDHash;

    /**
     * @var int
     */
    private $priority;

    /**
     * @var bool
     */
    private $isSingleAction;

    /**
     * @var string
     */
    private $recurrence;

    /**
     * @var array
     */
    private $nodeSettings;

    /**
     * @var string
     */
    private $stepId;

    /**
     * @var array
     */
    private $stepData;

    /**
     * @var bool
     */
    private $isFinished;

    /**
     * @var string
     */
    private $whenToRun;

    /**
     * @var int
     */
    private $timestamp;

    public function __construct(
        HooksFacade $hooks,
        StepProcessorInterface $stepProcessor,
        CronInterface $cron,
        CronSchedulesModelInterface $cronSchedulesModel,
        string $pluginVersion,
        LoggerInterface $logger,
        \Closure $expirablePostModelFactory,
        ExecutionContextInterface $executionContext
    ) {
        $this->hooks = $hooks;
        $this->generalProcessor = $stepProcessor;
        $this->cron = $cron;
        $this->cronSchedulesModel = $cronSchedulesModel;
        $this->executionContext = $executionContext;
        $this->pluginVersion = $pluginVersion;
        $this->logger = $logger;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
    }

    private function getLogPrefix(): string
    {
        return sprintf(self::LOG_PREFIX, $this->workflowId);
    }

    public function setup(array $step, callable $actionCallback): void
    {
        try {
            $node = $this->getNodeFromStep($step);

            $this->stepId = $step['node']['id'];
            $this->stepData = $step['node']['data'];
            $this->stepSlug = $this->stepData['slug'];
            $this->nodeSettings = $this->getNodeSettings($node);
            $this->workflowId = $this->executionContext->getVariable('global.workflow.id');
            $this->recurrence = $this->nodeSettings['schedule']['recurrence'] ?? self::SCHEDULE_RECURRENCE_SINGLE;
            $this->isSingleAction = self::SCHEDULE_RECURRENCE_SINGLE === $this->recurrence;
            $this->whenToRun = $this->nodeSettings['schedule']['whenToRun'] ?? self::WHEN_TO_RUN_NOW;

            // Calculate timestamp before generating UID so it can be included in the UID
            $this->timestamp = $this->getCalculatedTimestamp();

            /**
             * @param bool $useTimestamp
             * @param int $workflowId
             * @param array $step
             *
             * @return bool
             */
            $useTimestamp = (bool) $this->hooks->applyFilters(
                HooksAbstract::FILTER_SHOULD_USE_TIMESTAMP_ON_ACTION_UID,
                true,
                $this->workflowId,
                $step,
            );

            $this->actionUID = $this->getScheduledActionUniqueId($node, $useTimestamp, $this->timestamp);
            $this->actionUIDHash = md5($this->actionUID);
            $this->priority = (int)($this->nodeSettings['schedule']['priority'] ?? self::DEFAULT_PRIORITY);
            $this->isFinished = WorkflowScheduledStepModel::getMetaIsFinished($this->workflowId, $this->actionUIDHash);

            if ($this->shouldSkipScheduling()) {
                $this->logger->debugWithArgs(
                    $this->getLogPrefix() . 'Skipping scheduling for step %s',
                    $this->stepSlug
                );

                return;
            }

            $this->executionContext->setVariable($this->stepSlug, [
                'schedule_date' => date('Y-m-d H:i:s', $this->timestamp),
                'action_uid_hash' => $this->actionUIDHash,
                'repeat_count' => 0,
                'repeat_limit' => 0,
            ]);

            $this->scheduleAction();
        } catch (Throwable $e) {
            $this->logger->errorWithArgs(
                $this->getLogPrefix() . 'Failed to schedule workflow step "%s". File: %s:%d',
                $this->stepSlug,
                $e->getFile(),
                $e->getLine()
            );

            throw $e;
        }
    }

    private function getActionArgs(): array
    {
        $actionArgs = [
            'workflowId' => $this->workflowId,
            'workflowExecutionId' => $this->executionContext->getVariable('global.workflow.execution_id'),
            'stepId' => $this->stepId,
            'stepLabel' => $this->stepData['label'] ?? null,
            'stepName' => $this->stepData['name'],
            'pluginVersion' => $this->pluginVersion,
            'actionUIDHash' => $this->actionUIDHash,
            // This is not always set, only for some post-related triggers. Used to keep the post ID as reference.
            'postId' => $this->executionContext->getVariable('global.trigger.postId'),
        ];

        $actionArgs = $this->hooks->applyFilters(
            HooksAbstract::FILTER_CRON_SCHEDULE_RUNNER_ACTION_ARGS,
            $actionArgs,
            $this->workflowId,
            $this->stepData,
            $this->actionUID,
            $this->executionContext
        );

        return $actionArgs;
    }

    private function saveScheduledStepData(int $scheduledActionId)
    {
        /*
         * Setting the action ID is crucial for retrieving scheduled step arguments
         * from the wp_ppfuture_workflow_scheduled_steps table, specifically for recurring actions.
         * This step ensures that runtime data in the runtimeVariables field is properly
         * passed from the just-executed action to any new recurring instances.
         * Without this, we would lose important context between recurring executions.
         */
        $argsModel = new ScheduledActionModel();
        $argsModel->loadByActionId($scheduledActionId);
        $argsModel->setActionIdOnArgs();
        $argsModel->update();

        $compactedArgs = $this->compactArguments($this->stepSlug, $this->stepId);

        $scheduledStepModel = new WorkflowScheduledStepModel();
        $scheduledStepModel->setActionId($scheduledActionId);
        $scheduledStepModel->setWorkflowId($this->workflowId);
        $scheduledStepModel->setStepId($this->stepId);
        $scheduledStepModel->setActionUID($this->actionUID);
        $scheduledStepModel->setArgs($compactedArgs);
        $scheduledStepModel->setTotalRunCount(0);
        $scheduledStepModel->setIsRecurring(! $this->isSingleAction);

        $postId = (int)($this->executionContext->getVariable('global.trigger.postId') ?? 0);
        if ($postId) {
            $scheduledStepModel->setPostId($postId);
        }

        if (! $this->isSingleAction) {
            $scheduledStepModel->setRepeatUntil($this->nodeSettings['schedule']['repeatUntil'] ?? 'forever');
            $scheduledStepModel->setRepeatTimes((int)$this->nodeSettings['schedule']['repeatTimes'] ?? 0);
            $scheduledStepModel->setRepeatUntilDate($this->nodeSettings['schedule']['repeatUntilDate'] ?? '');
            $scheduledStepModel->setRepetitionNumber(0);
        }

        $scheduledStepModel->insert();
    }

    private function getCalculatedTimestamp(): int
    {
        $shouldRunNow = $this->whenToRun === self::WHEN_TO_RUN_NOW;

        if ($this->isSingleAction && $shouldRunNow) {
            return 0;
        }

        $timestamp = $this->calculateTimestamp(
            $this->whenToRun,
            $this->nodeSettings['schedule']['dateSource'] ?? self::DATE_SOURCE_CALENDAR,
            $this->nodeSettings['schedule']['specificDate'] ?? '',
            $this->nodeSettings['schedule']['customDateSource']['expression'] ?? '',
            $this->nodeSettings['schedule']['dateOffset'] ?? ''
        );

        if (is_null($timestamp)) {
            $timestamp = 0;
        }

        return (int) $timestamp;
    }

    private function scheduleSingleAction(array $actionArgs): int
    {
        if (self::WHEN_TO_RUN_NOW === $this->whenToRun) {
            return $this->scheduleImmediateAction($actionArgs);
        }

        return $this->scheduleDelayedSingleAction($actionArgs);
    }

    private function scheduleImmediateAction(array $actionArgs): int
    {
        $scheduledActionId = $this->cron->scheduleAsyncAction(
            HooksAbstract::ACTION_SCHEDULED_STEP_EXECUTE,
            [$actionArgs],
            false,
            $this->priority
        );

        $this->logger->debugWithArgs(
            $this->getLogPrefix() . 'Step "%s" scheduled for immediate execution with async action ID: %d',
            $this->stepSlug,
            $scheduledActionId
        );

        return $scheduledActionId;
    }

    private function scheduleDelayedSingleAction(array $actionArgs): int
    {
        $scheduledActionId = $this->cron->scheduleSingleAction(
            $this->timestamp,
            HooksAbstract::ACTION_SCHEDULED_STEP_EXECUTE,
            [$actionArgs],
            false,
            $this->priority
        );

        $this->logger->debugWithArgs(
            $this->getLogPrefix() . 'Step %s scheduled as a single action with ID %d',
            $this->stepSlug,
            $scheduledActionId
        );

        return $scheduledActionId;
    }

    private function scheduleAction(): void
    {
        $actionArgs = $this->getActionArgs();

        $scheduledActionId = $this->isSingleAction ?
            $this->scheduleSingleAction($actionArgs) :
            $this->scheduleRecurringAction($actionArgs);

        // If the action is scheduled, we need to set the action ID in the scheduled step arguments
        if ($scheduledActionId <= 0) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Failed to schedule action for step %s - no action ID was generated',
                $this->stepSlug
            );

            return;
        }

        $this->saveScheduledStepData($scheduledActionId);

        $this->logger->debugWithArgs(
            $this->getLogPrefix() . 'Successfully stored workflow step arguments for step "%s" with scheduled action ID %d',
            $this->stepSlug,
            $scheduledActionId
        );
    }

    private function scheduleRecurringAction(array $actionArgs): int
    {
        $scheduledActionId = 0;

        $interval = $this->getInterval();

        if ($interval <= 0) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Cannot schedule recurring step %s: Interval value must be greater than 0.',
                $this->stepSlug
            );

            return 0;
        }

        // Schedule a recurring action
        $scheduledActionId = $this->cron->scheduleRecurringActionInSeconds(
            $this->timestamp,
            $interval,
            HooksAbstract::ACTION_SCHEDULED_STEP_EXECUTE,
            [$actionArgs],
            false,
            $this->priority
        );

        $this->logger->debugWithArgs(
            $this->getLogPrefix() . 'Step "%s" scheduled as recurring action with ID %d',
            $this->stepSlug,
            $scheduledActionId
        );

        return $scheduledActionId;
    }

    private function getInterval(): int
    {
        if (self::SCHEDULE_RECURRENCE_CUSTOM === $this->recurrence) {
            return $this->getCustomRecurrenceInterval();
        }

        return $this->getCronScheduleValueByName($this->recurrence);
    }

    private function getCustomRecurrenceInterval(): int
    {
        $interval = (int)$this->nodeSettings['schedule']['repeatInterval'] ?? 0;

        /**
         * @param int $interval
         * @param array $nodeSettings
         * @param ExecutionContextInterface $executionContext
         *
         * @return int
         */
        $interval = $this->hooks->applyFilters(
            HooksAbstract::FILTER_INTERVAL_IN_SECONDS,
            $interval,
            $this->nodeSettings,
            $this->executionContext
        );

        return $interval;
    }

    private function getCronScheduleValueByName(string $recurrence): int
    {
        $recurrence = preg_replace('/^cron_/', '', $this->recurrence);

        return $this->cronSchedulesModel->getCronScheduleValueByName($recurrence);
    }

    private function shouldSkipScheduling(): bool
    {
        if (empty($this->timestamp) && $this->whenToRun !== self::WHEN_TO_RUN_NOW) {
            $this->logger->debugWithArgs($this->getLogPrefix() . 'Detected empty timestamp: Cannot schedule step %s', $this->stepSlug);

            return true;
        }

        // If a repeating action action has finished, we should not schedule it again.
        if (! $this->isSingleAction && $this->isFinished) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Detected finished step: Step %s has already finished, skipping',
                $this->stepSlug
            );

            return true;
        }

        /**
         * @param bool $shouldSkip
         * @param int $workflowId
         * @param string $stepId
         * @param string $actionUIDHash
         * @param ExecutionContextInterface $executionContext
         * @param array $stepData
         *
         * @return bool
         */
        $shouldSkip = $this->hooks->applyFilters(
            HooksAbstract::FILTER_SHOULD_SKIP_SCHEDULING,
            false,
            $this->workflowId,
            $this->stepId,
            $this->actionUIDHash,
            $this->executionContext,
            $this->stepData
        );

        if ($shouldSkip) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Skipping scheduling for step "%s" due to filter',
                $this->stepSlug
            );
        }

        return $shouldSkip;
    }

    private function getScheduledActionUniqueId(array $node, $useTimestamp = true, ?int $scheduledTimestamp = null): string
    {
        $uniqueId = [
            'workflowId' => $this->workflowId,
            'stepId' => $node['id'],
        ];

        if ($useTimestamp) {
            // Use scheduled timestamp if provided, otherwise use current time
            $uniqueId['timestamp'] = $scheduledTimestamp !== null ? $scheduledTimestamp : time();
        }

        if (isset($node['data']['settings']['schedule']['uniqueIdExpression'])) {
            $uniqueIdExpression = $node['data']['settings']['schedule']['uniqueIdExpression'];

            if (is_array($uniqueIdExpression)) {
                $uniqueIdExpression = $uniqueIdExpression['expression'];
            }

            if (! empty($uniqueIdExpression)) {
                $uniqueId = [
                    'custom' => $this->executionContext->resolveExpressionsInText($uniqueIdExpression),
                ];
            }
        }

        return wp_json_encode($uniqueId);
    }

    public function compactArguments(string $stepSlug, string $stepId): array
    {
        $this->logger->debugWithArgs(
            $this->getLogPrefix() . 'Compacting step %s arguments',
            $stepSlug
        );

        $compactedArgs = [
            'pluginVersion' => $this->pluginVersion,
            'step' => [
                'nodeId' => $stepId,
            ],
            'runtimeVariables' => $this->executionContext->getCompactedRuntimeVariables(),
        ];

        return $compactedArgs;
    }

    private function getPostDifferences($post1, $post2)
    {
        $differences = [];

        foreach ($post1 as $key => $value) {
            if (! isset($post2->$key)) {
                $differences[$key] = $value;
            } elseif ($post2->$key !== $value) {
                $differences[$key] = $value;
            }
        }

        return $differences;
    }

    public function expandArguments(array $compactArguments): array
    {
        $this->logger->debugWithArgs(
            $this->getLogPrefix() . 'Expanding step %s arguments',
            $compactArguments['step']['nodeId']
        );

        if (isset($compactArguments['step']['nodeId'])) {
            // New format where the step is compacted
            $nodeId = $compactArguments['step']['nodeId'];

            // Convert legacy context variables to runtime variables
            if (isset($compactArguments['contextVariables'])) {
                $compactArguments['runtimeVariables'] = $compactArguments['contextVariables'];
                unset($compactArguments['contextVariables']);
            }

            $workflowId = $compactArguments['runtimeVariables']['global']['workflow']['value'];

            $step = $this->getStepFromNodeId($workflowId, $nodeId);
        } else {
            // Old format, where the step is not compacted
            $step = $compactArguments['step'];
        }

        // Before v3.4.1 the pluginVersion was not included in the compacted arguments
        $isLegacyCompact = ! isset($compactArguments['pluginVersion']);

        $expandedArgs = [
            'step' => $step,
            'actionId' => $compactArguments['actionId'],
            'runtimeVariables' => $this->executionContext->expandRuntimeVariables(
                $compactArguments['runtimeVariables'],
                $isLegacyCompact
            ),
        ];

        return $expandedArgs;
    }

    private function markStepAsFinished(int $actionId): void
    {
        $scheduledStepModel = new WorkflowScheduledStepModel();
        $scheduledStepModel->loadByActionId($actionId);
        $scheduledStepModel->markAsFinished();
    }

    public function cancelScheduledStep(int $actionId, array $originalArgs): void
    {
        $scheduledActionModel = new ScheduledActionModel();
        $scheduledActionModel->loadByActionId($actionId);
        $scheduledActionModel->cancel();

        $this->cancelFutureRecurringActions($originalArgs['workflowId'], $originalArgs['stepId'], $originalArgs['actionUIDHash']);

        $this->logger->debugWithArgs(
            $this->getLogPrefix() . 'Step %s scheduled action cancelled',
            $originalArgs['stepId']
        );
    }

    private function cancelFutureRecurringActions(int $workflowId, string $stepId, string $actionUIDHash): void
    {
        $this->cron->scheduleSingleAction(
            time() + self::UNSCHEDULE_FUTURE_ACTION_DELAY,
            HooksAbstract::ACTION_UNSCHEDULE_RECURRING_STEP_ACTION,
            [
                'workflowId' => $workflowId,
                'actionUIDHash' => $actionUIDHash,
            ],
            false,
            10
        );

        $this->logger->debugWithArgs(
            $this->getLogPrefix() . 'Scheduled cleanup of future recurring actions for step %s',
            $stepId
        );
    }

    public function completeScheduledStep(int $actionId): void
    {
        $scheduledActionModel = new ScheduledActionModel();
        $scheduledActionModel->loadByActionId($actionId);
        $scheduledActionModel->complete();

        $this->markStepAsFinished($actionId);

        $this->logger->debugWithArgs(
            $this->getLogPrefix() . 'Successfully completed scheduled action ID %d',
            $actionId
        );
    }

    public function actionCallback(array $compactedArgs, array $originalArgs, bool $triggerCallbackIsRunning = false)
    {
        $expandedArgs = $this->expandArguments($compactedArgs);

        $workflowId = $compactedArgs['workflowId'];

        // Update the execution context with the expanded arguments from the original event
        $this->executionContext->setAllVariables($expandedArgs['runtimeVariables']);
        $this->executionContext->setWorkflowId($workflowId);

        if ($triggerCallbackIsRunning) {
            $this->triggerCallbackIsRunning();
        }


        // Check if the workflow is still active
        $workflowModel = new WorkflowModel();
        $workflowModel->load($workflowId);

        $actionId = $expandedArgs['actionId'];

        if (! $workflowModel->isActive()) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Workflow "%s" (ID: %d) is inactive. Scheduled action "%s" will be cancelled.',
                $workflowModel->getTitle(),
                $workflowId,
                $actionId
            );

            // TODO: Log this into the scheduler log
            $this->cancelScheduledStep($actionId, $originalArgs);

            return;
        }

        $scheduledStepModel = new WorkflowScheduledStepModel();
        $scheduledStepModel->loadByActionId($actionId);

        $isRecurrent = $scheduledStepModel->getIsRecurring();
        $isFinished = $scheduledStepModel->isFinished();
        $stepSlug = $expandedArgs['step']['node']['data']['slug'];

        if ($isRecurrent && $isFinished) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Detected finished step: Step "%s" has already finished, cancelling scheduled action',
                $stepSlug
            );

            $this->cancelScheduledStep($actionId, $originalArgs);

            return;
        }

        $markAsCompletedAfterExecution = false;
        $shouldExecute = true;

        if ($isRecurrent) {
            // Check if the node has a limit of executions. Default is 'forever'.
            $repeatUntil = $scheduledStepModel->getRepeatUntil();
            $totalRunCount = (int)$scheduledStepModel->getTotalRunCount();

            $expandedArgs['runtimeVariables'][$stepSlug]['repeat_count'] = $totalRunCount + 1;

            if ($repeatUntil === 'date') {
                $repeatUntilDate = strtotime($scheduledStepModel->getRepeatUntilDate() ?? '');
                $now = time();

                if ($repeatUntilDate <= $now) {
                    $markAsCompletedAfterExecution = true;
                }
            } elseif ($repeatUntil === 'times') {
                $runLimit = (int)$scheduledStepModel->getRepeatTimes() ?? self::DEFAULT_REPEAT_UNTIL_TIMES;

                $expandedArgs['runtimeVariables'][$stepSlug]['repeat_limit'] = $runLimit;

                // Will this be the last execution?
                if ($totalRunCount >= $runLimit - 1) {
                    $markAsCompletedAfterExecution = true;

                    $this->logger->debugWithArgs(
                        $this->getLogPrefix() . 'Step "%s" will be executed for the last time',
                        $stepSlug,
                        $runLimit
                    );
                }

                if ($totalRunCount >= $runLimit) {
                    $shouldExecute = false;
                    $markAsCompletedAfterExecution = true;

                    $this->logger->debugWithArgs(
                        $this->getLogPrefix() . 'Step "%s" has reached the run limit of %d times and will not be executed again',
                        $stepSlug,
                        $runLimit
                    );
                }
            }
        }

        if ($shouldExecute) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Executing step "%s"',
                $stepSlug
            );

            $this->executionContext->setAllVariables($expandedArgs['runtimeVariables']);
            $this->runNextSteps($expandedArgs['step']);

            $scheduledStepModel->incrementTotalRunCount();
            $scheduledStepModel->setRepetitionNumber($scheduledStepModel->getTotalRunCount());
            $scheduledStepModel->updateLastRunAt();
            $scheduledStepModel->update();
        }

        if ($markAsCompletedAfterExecution) {
            $this->logger->debugWithArgs(
                $this->getLogPrefix() . 'Scheduled action with ID "%d" has been successfully completed',
                $actionId
            );

            $this->completeScheduledStep($actionId);
            $this->cancelFutureRecurringActions($workflowId, $originalArgs['stepId'], $originalArgs['actionUIDHash']);

            $this->runNextSteps($expandedArgs['step'], 'finished');
            return;
        }
    }

    public function isScheduled(string $actionUIDHash): bool
    {
        try {
            $scheduledActionModel = new ScheduledActionModel();
            $scheduledActionModel->loadByActionArg('actionUIDHash', $actionUIDHash, ['pending', 'in-progress']);

            return true;
        } catch (Exception $e) {
            return false;
        }
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
        $nodeSettings = $this->generalProcessor->getNodeSettings($node);

        if (! isset($nodeSettings['schedule'])) {
            $nodeSettings['schedule'] = [];
        }

        return $nodeSettings;
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

    public function cancelWorkflowScheduledActions(int $workflowId): void
    {
        $scheduledActionsModel = new ScheduledActionsModel();
        $scheduledActionsModel->cancelWorkflowScheduledActions($workflowId);
    }

    private function getStepFromNodeId(int $workflowId, string $nodeId): array
    {
        $workflowModel = new WorkflowModel();
        $workflowModel->load($workflowId);
        $routineTree = $workflowModel->getPartialRoutineTreeFromNodeId($nodeId);

        return $routineTree;
    }

    public function executeSafelyWithErrorHandling(array $step, callable $callback, ...$args): void
    {
        $this->generalProcessor->executeSafelyWithErrorHandling($step, $callback, ...$args);
    }

    /**
     * @deprecated 4.10.0 Use the logger instead
     */
    public function prepareLogMessage(string $message, ...$args): string
    {
        return $this->generalProcessor->prepareLogMessage($message, ...$args);
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
}
