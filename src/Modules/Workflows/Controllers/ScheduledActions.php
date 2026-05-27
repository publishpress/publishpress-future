<?php

namespace PublishPress\Future\Modules\Workflows\Controllers;

use Exception;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use PublishPress\Future\Modules\Expirator\Interfaces\CronInterface;
use PublishPress\Future\Core\HooksAbstract as CoreHooksAbstract;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use PublishPress\Future\Modules\Workflows\HooksAbstract as WorkflowsHooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\StepTypesModelInterface;
use PublishPress\Future\Modules\Workflows\Models\ScheduledActionModel;
use PublishPress\Future\Modules\Workflows\Models\ScheduledActionsModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowScheduledStepModel;
use Throwable;

class ScheduledActions implements InitializableInterface
{
    /**
     * The default interval in seconds that the setup should be skipped.
     *
     * @var int
     */
    public const DEFAULT_SETUP_INTERVAL = 5;

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var StepTypesModelInterface
     */
    private $stepTypesModel;

    /**
     * @var CronInterface
     */
    private $cron;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        HookableInterface $hooks,
        StepTypesModelInterface $stepTypesModel,
        CronInterface $cron,
        SettingsFacade $settingsFacade,
        LoggerInterface $logger
    ) {
        $this->hooks = $hooks;
        $this->stepTypesModel = $stepTypesModel;
        $this->cron = $cron;
        $this->settingsFacade = $settingsFacade;
        $this->logger = $logger;
    }

    public function initialize()
    {
        $this->hooks->addFilter(
            HooksAbstract::FILTER_ACTION_SCHEDULER_LIST_COLUMN_HOOK,
            [$this, 'showTitleInHookColumn'],
            10,
            2
        );

        $this->hooks->addFilter(
            WorkflowsHooksAbstract::FILTER_ACTION_SCHEDULER_LIST_COLUMN_ARGS,
            [$this, 'showArgsInArgsColumn'],
            10,
            2
        );

        $this->hooks->addFilter(
            WorkflowsHooksAbstract::FILTER_ACTION_SCHEDULER_LIST_COLUMN_RECURRENCE,
            [$this, 'showRecurrenceInRecurrenceColumn'],
            10,
            2
        );

        $this->hooks->addAction(
            CoreHooksAbstract::ACTION_ADMIN_ENQUEUE_SCRIPTS,
            [$this, 'enqueueScripts'],
            10,
            2
        );

        $this->hooks->addAction(
            WorkflowsHooksAbstract::ACTION_INIT,
            [$this, 'scheduleOrphanWorkflowArgsCleanup'],
            21
        );

        $this->hooks->addAction(
            WorkflowsHooksAbstract::ACTION_INIT,
            [$this, 'scheduleFinishedScheduledStepsCleanup'],
            21
        );


        $this->hooks->addAction(
            WorkflowsHooksAbstract::ACTION_CLEANUP_ORPHAN_WORKFLOW_ARGS,
            [$this, 'deleteOrphanWorkflowArgs']
        );

        $this->hooks->addAction(
            WorkflowsHooksAbstract::ACTION_CLEANUP_FINISHED_SCHEDULED_STEPS,
            [$this, 'deleteExpiredDoneActions']
        );

        $this->hooks->addAction(
            WorkflowsHooksAbstract::ACTION_SCHEDULER_STORED_ACTION,
            [$this, 'storeWorkflowCompactedArgsForStoredAction'],
            10
        );

        $this->hooks->addFilter(
            HooksAbstract::FILTER_ACTION_SCHEDULER_ADMIN_NOTICE,
            [$this, 'filterActionSchedulerAdminNotice'],
            15,
            3
        );
    }

    private function getStepFromActionId(string $actionId): array
    {
        $actionModel = new ScheduledActionModel();
        $actionModel->loadByActionId((int) $actionId);

        $args = $actionModel->getArgs();

        if (empty($args)) {
            return [];
        }

        if (isset($args[0])) {
            $args = $args[0];
        }

        if (ScheduledActionModel::argsAreOnNewFormat((array) $args)) {
            try {
                $scheduledStepModel = new WorkflowScheduledStepModel();
                $scheduledStepModel->loadByActionId($actionModel->getActionId());

                $args = $scheduledStepModel->getArgs();
            } catch (Exception $e) {
                return [];
            }
        }

        if (! isset($args['runtimeVariables']['global']['workflow'])) {
            return [];
        }

        $stepIsCompact = ! isset($args['step']['next']);

        if ($stepIsCompact) {
            $workflowModel = new WorkflowModel();
            $workflowModel->load($args['runtimeVariables']['global']['workflow']['value'] ?? 0);

            $step = $workflowModel->getNodeById($args['step']['nodeId']);
        } else {
            $step = isset($args['step']) ? $args['step'] : [];
        }

        return $step;
    }

    public function showTitleInHookColumn($title, $row)
    {
        try {
            $actionModel = new ScheduledActionModel();
            $actionModel->loadByActionId($row['ID']);

            $hook = $actionModel->getHook();

            switch ($hook) {
                case WorkflowsHooksAbstract::ACTION_SCHEDULED_STEP_EXECUTE:
                    $step = $this->getStepFromActionId($row['ID']);

                    if (empty($step)) {
                        break;
                    }

                    $stepModel = new WorkflowScheduledStepModel();
                    $stepModel->loadByActionId($row['ID']);

                    $title = $stepModel->getIsRecurring() ?
                        __('Workflow repeating scheduled step', 'post-expirator') :
                        __('Workflow scheduled step', 'post-expirator');

                    break;

                case WorkflowsHooksAbstract::ACTION_UNSCHEDULE_RECURRING_STEP_ACTION:
                case WorkflowsHooksAbstract::ACTION_UNSCHEDULE_RECURRING_NODE_ACTION:
                    $title = __('Unschedule workflow recurring scheduled step', 'post-expirator');
                    break;

                case WorkflowsHooksAbstract::ACTION_CLEANUP_ORPHAN_WORKFLOW_ARGS:
                    $title = __('Cleanup orphan workflow scheduled step arguments', 'post-expirator');
                    break;

                case WorkflowsHooksAbstract::ACTION_CLEANUP_FINISHED_SCHEDULED_STEPS:
                    $title = sprintf(
                        __('Clean up completed scheduled steps older than %d days', 'post-expirator'),
                        $this->settingsFacade->getScheduledWorkflowStepsCleanupRetention()
                    );
                    break;
            }
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf(
                    'Error customizing title "%s" in hook column: %s. File: %s:%d',
                    $title,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
        }

        return $title;
    }

    public function filterActionSchedulerAdminNotice($html, $action, $notification)
    {
        if ($action->get_group() !== 'publishpress-future') {
            return $html;
        }

        $hook = $action->get_hook();

        switch ($hook) {
            case WorkflowsHooksAbstract::ACTION_WORKFLOW_SAVED:
                $args = $action->get_args();

                if (isset($args['postId']) && isset($args['workflow']) && 'expire' === $args['workflow']) {
                    $transientName = 'post-expirator-notice-' . (int) $args['postId'];
                    $noticeMessage = get_transient($transientName);
                    delete_transient($transientName);

                    // translators: %s is the action description
                    $html = sprintf(
                        __('Successfully executed action: %s', 'post-expirator'),
                        $noticeMessage
                    );
                }
                break;

            case WorkflowsHooksAbstract::ACTION_SCHEDULED_STEP_EXECUTE:
                $html = __('Executed workflow scheduled step', 'post-expirator');
                break;

            case WorkflowsHooksAbstract::ACTION_CLEANUP_FINISHED_SCHEDULED_STEPS:
                $days = $this->settingsFacade->getScheduledWorkflowStepsCleanupRetention();

                $html = sprintf(
                    __('Cleaned up completed scheduled steps older than %d days', 'post-expirator'),
                    $days
                );
                break;

            case WorkflowsHooksAbstract::ACTION_CHECK_EXPIRED_ACTIONS:
                $html = __('Checked for expired actions', 'post-expirator');
                break;

            case WorkflowsHooksAbstract::ACTION_CLEANUP_ORPHAN_WORKFLOW_ARGS:
                $html = __('Cleaned up orphan workflow scheduled step arguments', 'post-expirator');
                break;

            case WorkflowsHooksAbstract::ACTION_UNSCHEDULE_RECURRING_STEP_ACTION:
                $html = __('Unscheduled workflow recurring scheduled step', 'post-expirator');
                break;
        }

        return $html;
    }

    public function showArgsInArgsColumn($html, $row)
    {
        try {
            $actionId = $row['ID'];

            $actionModel = new ScheduledActionModel();
            $actionModel->loadByActionId($actionId);

            $hook = $actionModel->getHook();
            $args = $actionModel->getArgs();

            if (empty($args)) {
                return $html;
            }

            if (isset($args[0])) {
                $args = $args[0];
            }

            switch ($hook) {
                case WorkflowsHooksAbstract::ACTION_ASYNC_EXECUTE_NODE:
                case WorkflowsHooksAbstract::ACTION_SCHEDULED_STEP_EXECUTE:
                    if (ScheduledActionModel::argsAreOnNewFormat((array) $args)) {
                        $scheduledStepModel = new WorkflowScheduledStepModel();
                        $scheduledStepModel->loadByActionId($actionId);

                        $args = $scheduledStepModel->getArgs();
                    }

                    if (! isset($args['runtimeVariables']['global']['workflow'])) {
                        return $html;
                    }

                    $argsText = '';

                    // Before v3.4.1 the plugin version was not set in the arguments
                    if (isset($args['pluginVersion'])) {
                        $workflowId = $args['runtimeVariables']['global']['workflow']['value'] ?? 0;
                    } else {
                        $workflowId = $args['runtimeVariables']['global']['workflow'] ?? 0;
                    }

                    $workflowModel = new WorkflowModel();
                    $workflowModel->load($workflowId);

                    $workflowTitle = $workflowModel->getTitle();
                    $stepIsCompact = ! isset($args['step']['next']);

                    $step = null;
                    if ($stepIsCompact) {
                        $step = $workflowModel->getPartialRoutineTreeFromNodeId($args['step']['nodeId']);
                    } else {
                        $step = $args['step'];
                    }

                    // The step is not found anymore in the workflow.
                    if (
                        empty($step)
                        || ! isset($step['node'])
                        || ! isset($step['node']['data'])
                        || ! isset($step['node']['data']['name'])
                    ) {
                        $html = '<span style="color: red;">' . esc_html__('Schedule step is required for this workflow.', 'post-expirator') . '</span>';

                        $html .= '<br>';
                        $html .= '<strong>' . esc_html__('Workflow:', 'post-expirator') . '</strong> '
                            . esc_html($workflowTitle) . '<br>';

                        $html .= '<strong>' . esc_html__('Step:', 'post-expirator') . '</strong> '
                            . esc_html($args['step']['nodeId']);

                        return $html;
                    }

                    $next = $step['next'] ?? [];

                    $stepType = $this->stepTypesModel->getStepType($step['node']['data']['name']);

                    $sourceHandles = [];
                    if (! is_null($stepType)) {
                        $handlesSchema = $stepType->getHandleSchema();

                        foreach ($handlesSchema['source'] as $handle) {
                            $sourceHandles[$handle['id']] = $handle['label'];
                        }
                    }

                    $nextNodes = '<ul class="future-workflows-outputs">';
                    foreach ($next as $handleId => $handlerNodes) {
                        $handleLabel = $sourceHandles[$handleId] ?? $handleId;
                        $nextNodes .= '<li class="future-workflow-step-handler">' . esc_html($handleLabel) . ':</li>';
                        $nextNodes .= '<ul>';
                        foreach ($handlerNodes as $nextStep) {
                            $stepLabel = '';
                            if (isset($nextStep['node']['data']['label'])) {
                                $stepLabel = $nextStep['node']['data']['label'];
                            }

                            if (empty($stepLabel)) {
                                $stepNodeType = $this->stepTypesModel->getStepType($nextStep['node']['data']['name']);
                                if (is_object($stepNodeType)) {
                                    $stepLabel = $stepNodeType->getLabel();
                                }
                            }

                            if (empty($stepLabel)) {
                                $stepLabel = $nextStep['node']['data']['name'];
                            }

                            $nextNodes .= '<li>' . esc_html($stepLabel) . '</li>';
                        }
                        $nextNodes .= '</ul>';
                    }
                    $nextNodes .= '</ul>';

                    $argsText = '<strong>' . esc_html__('Workflow:', 'post-expirator') . '</strong> '
                        . esc_html($workflowTitle) . '<br>';

                    if (isset($args['pluginVersion'])) {
                        $argsText .= '<strong>' . esc_html__('Trigger: ', 'post-expirator') . '</strong>'
                            . esc_html($args['runtimeVariables']['global']['trigger']['value']['label']) . '<br>';

                        // Check if the trigger is related to a post and show the post title and permalink
                        if (isset($args['runtimeVariables']['global']['trigger']['value']['slug'])) {
                            $nodeSlug = $args['runtimeVariables']['global']['trigger']['value']['slug'];

                            $postId = 0;

                            $commonNamesForPostVariables = [
                                'postId',
                                'post',
                                'postAfter',
                                'postBefore',
                            ];

                            foreach ($commonNamesForPostVariables as $commonName) {
                                if (isset($args['runtimeVariables'][$nodeSlug][$commonName])) {
                                    $postId = (int) $args['runtimeVariables'][$nodeSlug][$commonName]['value'];
                                    break;
                                }
                            }

                            if (! empty($postId)) {
                                $post = get_post($postId);

                                if ($post instanceof \WP_Post) {
                                    $postPermaling = get_permalink($post->ID);
                                    $argsText .= '<strong>' . esc_html__('Post:', 'post-expirator')
                                        . '</strong> <a target="_blank" href="' . esc_url($postPermaling) . '">'
                                        . esc_html($post->post_title) . '</a><br>';
                                }
                            }
                        }
                    } else {
                        $argsText .= '<strong>' . esc_html__('Trigger: ', 'post-expirator') . '</strong>'
                            . esc_html($args['runtimeVariables']['global']['trigger']['label']) . '<br>';
                    }

                    $argsText .= '<strong>' . esc_html__('Steps:', 'post-expirator') . '</strong><br>' . $nextNodes;

                    $html = $argsText;
                    break;

                case WorkflowsHooksAbstract::ACTION_UNSCHEDULE_RECURRING_STEP_ACTION:
                case WorkflowsHooksAbstract::ACTION_UNSCHEDULE_RECURRING_NODE_ACTION:
                    $html = esc_html__('Workflow recurring scheduled action', 'post-expirator');
                    break;
            }
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf(
                    'Error showing args for action %d in args column: %s. File: %s:%d',
                    $actionId,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
        }

        return $html;
    }

    public function showRecurrenceInRecurrenceColumn($recurrence, $row)
    {
        try {
            $step = $this->getStepFromActionId($row['ID']);

            if (empty($step)) {
                return $recurrence;
            }

            // Show the recurrence information
            $stepModel = new WorkflowScheduledStepModel();
            $stepModel->loadByActionId($row['ID']);

            if (! $stepModel->getIsRecurring()) {
                return $recurrence;
            }

            $repeatUntil = $stepModel->getRepeatUntil();

            // Repeat until forever
            if ($repeatUntil === 'forever') {
                return $recurrence;
            }

            // Repeat until a specific date
            if ($repeatUntil === 'date') {
                $dateFormat = get_option('date_format');
                $timeFormat = get_option('time_format');
                $dateTimeFormat = $dateFormat . ' ' . $timeFormat;

                $formattedDate = wp_date($dateTimeFormat, strtotime($stepModel->getRepeatUntilDate()));

                return sprintf(
                    /* translators: %1$s is the recurrence, %2$s is the date */
                    __('%1$s until %2$s', 'post-expirator'),
                    $recurrence,
                    $formattedDate
                );
            }

            // Repeat until a specific number of times
            if ($repeatUntil === 'times') {
                $recurrence = sprintf(
                    /* translators: %1$s is the recurrence, %2$d is the repeat times */
                    __('%1$s for %2$d times', 'post-expirator'),
                    $recurrence,
                    $stepModel->getRepeatTimes()
                );

                // Check how many times the step has been executed
                $executedTimes = $stepModel->getRepetitionNumber();

                $recurrence .= ' ' . sprintf(
                    /* translators: %1$s is the executed times, %2$d is the total repeat times */
                    __('[%1$s/%2$d]', 'post-expirator'),
                    $executedTimes,
                    $stepModel->getRepeatTimes()
                );

                return $recurrence;
            }
        } catch (Throwable $e) {
            $this->logger->error(
                sprintf(
                    'Error showing recurrence for action %d in recurrence column: %s. File: %s:%d',
                    $row['ID'],
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                )
            );
        }

        return $recurrence;
    }

    private function getExecutedTimes(array $step): int
    {
        $executedTimes = 0;

        return $executedTimes;
    }

    public function enqueueScripts($hook)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only comparing admin page slug; no form action.
        if (! isset($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== 'publishpress-future-scheduled-actions') {
            return;
        }

        wp_enqueue_style(
            "future_actions_admin_style",
            Plugin::getAssetUrl("css/future-actions.css"),
            ["wp-components", "wp-edit-post", "wp-editor"],
            PUBLISHPRESS_FUTURE_VERSION
        );
    }

    public function scheduleOrphanWorkflowArgsCleanup()
    {
        try {
            /**
             * @param int $interval
            * @return int
            */
            $interval = $this->hooks->applyFilters(
                WorkflowsHooksAbstract::FILTER_ORPHAN_WORKFLOW_ARGS_CLEANUP_INTERVAL,
                DAY_IN_SECONDS
            );

            if (! $this->verifyOperationTimeout('orphan_workflow_args_cleanup', $interval)) {
                return;
            }

            $this->cron->clearScheduledAction(
                WorkflowsHooksAbstract::ACTION_CLEANUP_ORPHAN_WORKFLOW_ARGS,
                [],
                false
            );

            $this->cron->scheduleRecurringActionInSeconds(
                time() + $interval,
                $interval,
                WorkflowsHooksAbstract::ACTION_CLEANUP_ORPHAN_WORKFLOW_ARGS,
                [],
                true
            );
        } catch (Throwable $th) {
            $this->logger->error('Error scheduling orphan workflow args cleanup: ' . $th->getMessage());
        }
    }

    public function scheduleFinishedScheduledStepsCleanup()
    {
        try {
            /**
             * @param int $interval
             * @return int
             */
            $interval = $this->hooks->applyFilters(
                WorkflowsHooksAbstract::FILTER_FINISHED_SCHEDULED_STEPS_CLEANUP_INTERVAL,
                DAY_IN_SECONDS
            );

            if (! $this->verifyOperationTimeout('finished_scheduled_steps_cleanup', $interval)) {
                return;
            }

            $this->cron->clearScheduledAction(
                WorkflowsHooksAbstract::ACTION_CLEANUP_FINISHED_SCHEDULED_STEPS,
                [],
                false
            );

            $this->cron->scheduleRecurringActionInSeconds(
                time() + $interval,
                $interval,
                WorkflowsHooksAbstract::ACTION_CLEANUP_FINISHED_SCHEDULED_STEPS,
                [],
                true
            );
        } catch (Throwable $th) {
            $this->logger->error('Error scheduling finished scheduled steps cleanup: ' . $th->getMessage());
        }
    }

    /**
     * Delete orphan workflow args.
     *
     * @return void
     */
    public function deleteOrphanWorkflowArgs()
    {
        (new ScheduledActionsModel())->deleteOrphanWorkflowArgs();
    }

    public function deleteExpiredDoneActions()
    {
        (new ScheduledActionsModel())->deleteExpiredDoneActions();

        $this->hooks->doAction(WorkflowsHooksAbstract::ACTION_DELETE_EXPIRED_DONE_ACTIONS);
    }

    /*
     * Method called when a scheduled action is stored. We use this to replicate some runtime
     * data from the context variables to the scheduled step model. Used specifically when the
     * recurrent scheduled step is scheduled.
     *
     * @param int $actionId
     * @return void
     */
    public function storeWorkflowCompactedArgsForStoredAction($actionId)
    {
        $actionModel = new ScheduledActionModel();
        $actionModel->loadByActionId($actionId);

        if ($actionModel->getHook() !== WorkflowsHooksAbstract::ACTION_SCHEDULED_STEP_EXECUTE) {
            return;
        }

        $args = $actionModel->getArgs();
        $oldActionId = $args[0]['actionId'] ?? 0;

        if (empty($oldActionId)) {
            return;
        }

        $oldScheduledStepModel = new WorkflowScheduledStepModel();
        $oldScheduledStepModel->loadByActionId($oldActionId);

        if (empty($oldScheduledStepModel->getActionId())) {
            return;
        }

        $oldScheduledArgs = $oldScheduledStepModel->getArgs();

        if (empty($oldScheduledArgs)) {
            return;
        }

        $newScheduledStepModel = new WorkflowScheduledStepModel();
        $newScheduledStepModel->setActionId($actionId);
        $newScheduledStepModel->setWorkflowId($oldScheduledStepModel->getWorkflowId());
        $newScheduledStepModel->setStepId($oldScheduledStepModel->getStepId());
        $newScheduledStepModel->setActionUID($oldScheduledStepModel->getActionUID());
        $newScheduledStepModel->setIsCompressed($oldScheduledStepModel->getIsCompressed());
        $newScheduledStepModel->setIsRecurring($oldScheduledStepModel->getIsRecurring());
        $newScheduledStepModel->setArgs($oldScheduledArgs);
        $newScheduledStepModel->setRepeatUntilDate($oldScheduledStepModel->getRepeatUntilDate());
        $newScheduledStepModel->setRepeatTimes($oldScheduledStepModel->getRepeatTimes());
        $newScheduledStepModel->setRepeatUntil($oldScheduledStepModel->getRepeatUntil());
        $newScheduledStepModel->setRepetitionNumber($oldScheduledStepModel->getRepetitionNumber());
        $newScheduledStepModel->insert();
    }

    private function verifyOperationTimeout(string $actionUid, ?int $timeout = null): bool
    {
        $transientKey = 'publishpressfuture_' . $actionUid;

        if (is_null($timeout)) {
            $timeout = self::DEFAULT_SETUP_INTERVAL;
        }

        /**
         * @param int $defaultTimeout
         *
         * @return int
         */
        $transientTimeout = $this->hooks->applyFilters(
            WorkflowsHooksAbstract::FILTER_CLEANUP_SCHEDULED_TRANSIENT_TIMEOUT,
            $timeout
        );

        if (get_transient($transientKey)) {
            return false;
        }

        set_transient($transientKey, true, $transientTimeout);

        return true;
    }
}
