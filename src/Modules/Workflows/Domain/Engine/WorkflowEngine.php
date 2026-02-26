<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine;

use Closure;
use Exception;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\NodeResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\SiteResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\UserResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\WorkflowResolver;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextRegistryInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowEngineInterface;
use PublishPress\Future\Modules\Workflows\Models\ScheduledActionModel;
use PublishPress\Future\Modules\Workflows\Models\ScheduledActionsModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowScheduledStepModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowsModel;
use PublishPress\Future\Modules\Workflows\Module;
use PublishPress\Future\Modules\Workflows\Interfaces\TriggerRunnerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepTypesModelInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowModelInterface;
use Throwable;

use function random_bytes;
use function bin2hex;

class WorkflowEngine implements WorkflowEngineInterface
{
    public const LOG_PREFIX = '[WorkflowEngine] ';

    public const LOG_PREFIX_STEP = '[WorkflowEngine:%d]: ';

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var StepTypesModelInterface
     */
    private $stepTypesModel;

    /**
     * @var \Closure
     */
    private $stepRunnerFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $currentAsyncActionId;

    /**
     * @var InitializableInterface
     */
    private $executionContextInitializer;

    /**
     * @var ExecutionContextRegistryInterface
     */
    private $executionContextRegistry;

    /**
     * @var string
     */
    private $engineExecutionId;

    /**
     * @var string
     */
    private $engineExecutionEnvironment;

    /**
     * @var float
     */
    private $engineStartTime;

    public function __construct(
        HookableInterface $hooks,
        StepTypesModelInterface $stepTypesModel,
        \Closure $stepRunnerFactory,
        ExecutionContextRegistryInterface $executionContextRegistry,
        LoggerInterface $logger,
        InitializableInterface $executionContextInitializer
    ) {
        $this->hooks = $hooks;
        $this->stepTypesModel = $stepTypesModel;
        $this->stepRunnerFactory = $stepRunnerFactory;
        $this->executionContextRegistry = $executionContextRegistry;
        $this->logger = $logger;
        $this->executionContextInitializer = $executionContextInitializer;

        $this->hooks->doAction(HooksAbstract::ACTION_WORKFLOW_ENGINE_LOAD);

        $this->hooks->addAction(
            HooksAbstract::ACTION_EXECUTE_STEP,
            [$this, 'executeStepRoutine'],
            10,
            2
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_SCHEDULED_STEP_EXECUTE,
            [$this, "executeScheduledStepRoutine"],
            10
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_UNSCHEDULE_RECURRING_STEP_ACTION,
            [$this, "unscheduleRecurringStepAction"],
            10,
            2
        );

        /**
         * We are keeping the old constant for compatibility with old actions scheduled by the old constant.
         */
        $this->hooks->addAction(
            HooksAbstract::ACTION_UNSCHEDULE_RECURRING_NODE_ACTION,
            [$this, "unscheduleRecurringStepAction"],
            10,
            2
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_SCHEDULER_BEGIN_EXECUTE,
            [$this, "setCurrentAsyncActionId"]
        );

        $this->hooks->addAction(
            HooksAbstract::ACTION_POST_UPDATED,
            [$this, "onWorkflowUpdated"],
            10,
            3
        );
    }

    public function start()
    {
        $this->engineStartTime = microtime(true);

        $this->hooks->doAction(HooksAbstract::ACTION_WORKFLOW_ENGINE_START);

        $this->executionContextInitializer->initialize();

        $this->engineExecutionEnvironment = $this->getEngineExecutionEnvironment();

        $currentUser = $this->getCurrentUser();

        $this->engineExecutionId = $this->generateUniqueId();

        $this->logger->debugWithArgs(
            self::LOG_PREFIX . 'Engine starting with execution id "%s" for user #%s (%s)',
            $this->engineExecutionId,
            ($currentUser->ID > 0) ? (string) $currentUser->ID : 'guest',
            $this->engineExecutionEnvironment
        );

        /**
         * Action triggered when the engine starts.
         *
         * @param string $engineExecutionId The ID of the engine execution.
         */
        $this->hooks->doAction(
            HooksAbstract::ACTION_WORKFLOW_ENGINE_START_ENGINE,
            $this->engineExecutionId
        );

        $this->logger->debug(self::LOG_PREFIX . 'Engine ready, listening for triggers');

        $this->hooks->addAction('shutdown', [$this, 'onShutdown'], 10, 0);
    }

    /**
     * Log when the workflow engine has finished processing the request.
     *
     * @since 4.10.0
     * @return void
     */
    public function onShutdown(): void
    {
        if (! $this->logger->isDownloadLogRequested() && isset($this->engineStartTime)) {
            $elapsedMs = (int) round((microtime(true) - $this->engineStartTime) * 1000);
            $this->logger->debugWithArgs(
                self::LOG_PREFIX . 'Engine finished processing request (%d ms)',
                $elapsedMs
            );
        }
    }

    public function runWorkflows(array $workflowIdsToRun = [])
    {
        /**
         * Action triggered when the workflows are running.
         *
         * @param array $workflowIdsToRun The IDs of the workflows to run.
         */
        $this->hooks->doAction(
            HooksAbstract::ACTION_WORKFLOW_ENGINE_RUN_WORKFLOWS,
            $workflowIdsToRun
        );

        if (empty($workflowIdsToRun)) {
            $this->logger->debug(self::LOG_PREFIX . 'Loading all published workflows');

            $workflowIdsToRun = $this->getPublishedWorkflowsIds();
        }

        if (! empty($workflowIdsToRun)) {
            $this->logger->debugWithArgs(
                self::LOG_PREFIX . 'Loading %d workflows',
                count($workflowIdsToRun)
            );
        }

        $stepTypes = $this->getAllStepTypes();

        // Setup the workflow triggers
        foreach ($workflowIdsToRun as $workflowId) {
            $workflowId = (int) $workflowId;

            /** @var WorkflowModelInterface $workflow */
            $workflow = new WorkflowModel();
            $workflow->load($workflowId);

            /**
             * Action triggered when the workflow is initialized.
             *
             * @param int $workflowId The ID of the workflow to initialize.
             */
            $this->hooks->doAction(
                HooksAbstract::ACTION_WORKFLOW_ENGINE_INITIALIZE_WORKFLOW,
                $workflowId
            );

            $workflowExecutionId = $this->generateUniqueId();

            $this->logger->debugWithArgs(
                self::LOG_PREFIX_STEP . 'Initializing workflow "%s" with execution id "%s"',
                $workflowId,
                $workflow->getTitle(),
                $workflowExecutionId
            );

            $this->executionContextRegistry->getExecutionContext($workflowExecutionId);

            $triggerSteps = $workflow->getTriggerNodes();
            $routineTree = $workflow->getRoutineTree($stepTypes);

            $this->prepareExecutionContextForWorkflow(
                $workflowExecutionId,
                $workflow
            );

            foreach ($triggerSteps as $triggerStep) {
                $triggerName = $triggerStep['data']['name'];
                $triggerId = $triggerStep['id'];

                $stepType = $this->stepTypesModel->getStepType($triggerName);

                if (! $stepType) {
                    $this->logger->errorWithArgs(
                        self::LOG_PREFIX_STEP . 'Skipping trigger "%s" because step type not found',
                        $workflowId,
                        $triggerName
                    );

                    continue;
                }

                /** @var TriggerRunnerInterface $triggerRunner */
                $triggerRunner = call_user_func($this->stepRunnerFactory, $triggerName, $workflowExecutionId);

                if (is_null($triggerRunner)) {
                    $this->logger->errorWithArgs(
                        self::LOG_PREFIX_STEP . 'Skipping trigger "%s" because trigger runner not found',
                        $workflowId,
                        $triggerName
                    );

                    continue;
                }

                // Ignore if there is no routine tree for this trigger
                if (! isset($routineTree[$triggerId])) {
                    $this->logger->debugWithArgs(
                        self::LOG_PREFIX_STEP . 'Skipping trigger "%s" because no routine tree found',
                        $workflowId,
                        $triggerId
                    );

                    continue;
                }

                $this->prepareExecutionContextForTrigger(
                    $workflowExecutionId,
                    $triggerStep
                );

                // Setup the trigger
                $this->logger->debugWithArgs(
                    self::LOG_PREFIX_STEP . 'Registering trigger "%s"',
                    $workflowId,
                    $triggerStep['data']['slug']
                );

                /**
                 * Action triggered when the trigger is initialized.
                 *
                 * @param int $workflowId The ID of the workflow.
                 * @param string $triggerId The ID of the trigger.
                 */
                $this->hooks->doAction(
                    HooksAbstract::ACTION_WORKFLOW_ENGINE_SETUP_TRIGGER,
                    $workflowId,
                    $triggerId
                );

                $triggerRunner->setup($workflowId, $routineTree[$triggerId]);
            }

            $this->logger->debugWithArgs(
                self::LOG_PREFIX_STEP . 'Workflow is ready',
                $workflowId
            );
        }

        $this->logger->debug(self::LOG_PREFIX . 'All workflows are ready');

        /**
         * Action triggered when the workflows are initialized.
         */
        $this->hooks->doAction(
            HooksAbstract::ACTION_WORKFLOW_ENGINE_WORKFLOWS_INITIALIZED
        );
    }

    public function prepareExecutionContextForWorkflow(
        string $workflowExecutionId,
        WorkflowModelInterface $workflowModel
    ): void {
        $executionContext = $this->executionContextRegistry->getExecutionContext(
            $workflowExecutionId
        );

        $currentUser = $this->getCurrentUser();

        $globalVariables = [
            'engine_execution_id' => $this->engineExecutionId,
            'user' => new UserResolver($currentUser),
            'site' => new SiteResolver(),
            'workflow' => new WorkflowResolver(
                [
                    'id' => $workflowModel->getId(),
                    'title' => $workflowModel->getTitle(),
                    'description' => $workflowModel->getDescription(),
                    'modified_at' => $workflowModel->getModifiedAt(),
                    'execution_id' => $workflowExecutionId,
                    'execution_trace' => [],
                ]
            ),
        ];

        $executionContext->setVariable('global', $globalVariables);
        $executionContext->setWorkflowId($workflowModel->getId());
    }

    public function prepareExecutionContextForTrigger(
        string $workflowExecutionId,
        array $triggerStep
    ): void {
        $stepType = $this->stepTypesModel->getStepType($triggerStep['data']['name']);

        $triggerContext = new NodeResolver(
            [
                'id' => $triggerStep['id'],
                'name' => $triggerStep['data']['name'],
                'label' => $stepType->getLabel(),
                'activation_timestamp' => date('Y-m-d H:i:s'),
                'slug' => $triggerStep['data']['slug'],
                'postId' => 0,
            ]
        );

        $executionContext = $this->executionContextRegistry->getExecutionContext(
            $workflowExecutionId
        );
        $executionContext->setVariable('global.trigger', $triggerContext);
    }

    public function setCurrentAsyncActionId($actionId)
    {
        $this->currentAsyncActionId = $actionId;
    }

    public function getCurrentAsyncActionId(): int
    {
        return (int) $this->currentAsyncActionId;
    }

    public function executeStepRoutine($step, $workflowExecutionId)
    {
        $node = $step['node'];
        $nodeName = $node['data']['name'];

        $stepRunner = call_user_func($this->stepRunnerFactory, $nodeName, $workflowExecutionId);

        $executionContext = $this->executionContextRegistry->getExecutionContext($workflowExecutionId);

        $workflowId = $executionContext->getVariable('global.workflow.id');

        if (is_null($stepRunner)) {
            $message = sprintf(
                self::LOG_PREFIX_STEP . 'Step runner not found: %s',
                $workflowId,
                $nodeName
            );

            $this->logger->error($message);

            throw new \Exception(esc_html($message));
        }

        $this->logger->debugWithArgs(
            self::LOG_PREFIX_STEP . 'Executing step "%s"',
            $workflowId,
            $node['data']['slug']
        );

        /**
         * Action triggered when the step is initialized.
         *
         * @param array $step The step.
         */
        $this->hooks->doAction(
            HooksAbstract::ACTION_WORKFLOW_ENGINE_SETUP_STEP,
            $step,
        );

        $stepRunner->setup($step);
    }

    public function executeScheduledStepRoutine($args)
    {
        try {
            if (is_null($args)) {
                $message = self::LOG_PREFIX . 'Scheduled step runner error, no args found';

                $this->logger->error($message);

                throw new \Exception(esc_html($message));
            }

            $originalArgs = $args;

            /**
             * Action triggered when the scheduled step is executed.
             *
             * @param array $args The args of the scheduled step.
             */
            $this->hooks->doAction(
                HooksAbstract::ACTION_WORKFLOW_ENGINE_EXECUTE_SCHEDULED_STEP,
                $args
            );

            if (ScheduledActionModel::argsAreOnNewFormat($args)) {
                // New format, when the args are saved in the wp_ppfuture_workflow_scheduled_steps table.
                $nodeName = $args['stepName'];
                $scheduledStepModel = new WorkflowScheduledStepModel();
                $scheduledStepModel->loadByActionId($this->currentAsyncActionId);
                $args = $scheduledStepModel->getArgs();
                $workflowExecutionId = $args['runtimeVariables']['global']['workflow']['execution_id'];
            } else {
                // Old format, when the args were saved directly in the actionsscheduler_actions table.
                if (! isset($args['step']['node']['data']['name'])) {
                    $message = self::LOG_PREFIX . 'Scheduled step runner error, no step name found';

                    $this->logger->error($message);

                    return;
                }

                $nodeName = $args['step']['node']['data']['name'];

                $workflowExecutionId = '1_' . $this->generateUniqueId();
            }

            $args['actionId'] = $this->currentAsyncActionId;
            $args['workflowId'] = $originalArgs['workflowId'];

            $stepRunner = call_user_func($this->stepRunnerFactory, $nodeName, $workflowExecutionId);

            $workflow = new WorkflowModel();
            $workflow->load($originalArgs['workflowId']);

            $step = $workflow->getPartialRoutineTreeFromNodeId($originalArgs['stepId']);

            if (empty($step)) {
                throw new \Exception('Step not found');
            }

            $this->logger->debugWithArgs(
                self::LOG_PREFIX_STEP . 'Executing scheduled step "%s" (Action #%d)',
                $originalArgs['workflowId'],
                $step['node']['data']['slug'] ?? 'unknown',
                $args['actionId']
            );

            $stepRunner->actionCallback($args, $originalArgs);
        } catch (Throwable $e) {
            $this->logger->errorWithArgs(
                self::LOG_PREFIX . 'Scheduled step runner error: %s. File: %s:%d',
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );
        }
    }

    public function onWorkflowUpdated($workflowId, $newPost, $oldPost)
    {
        if ($newPost->post_type !== Module::POST_TYPE_WORKFLOW) {
            return;
        }

        // Ignore if the status is the same
        if ($oldPost->post_status === $newPost->post_status) {
            return;
        }

        if ($newPost->post_status === 'publish') {
            $this->onWorkflowPublished($workflowId);
        }
    }

    public function onWorkflowPublished($workflowId)
    {
        $scheduledActionsModel = new ScheduledActionsModel();
        $scheduledActionsModel->cancelWorkflowScheduledActions($workflowId);
    }

    public function unscheduleRecurringStepAction($workflowId, $actionUIDHash)
    {
        $scheduledActionsModel = new ScheduledActionsModel();
        $scheduledActionsModel->cancelRecurringScheduledActions($workflowId, $actionUIDHash);
    }

    public function getEngineExecutionId(): string
    {
        return $this->engineExecutionId;
    }

    public function generateUniqueId(): string
    {
        return bin2hex(random_bytes(16));
    }

    public function getExecutionContextRegistry(): ExecutionContextRegistryInterface
    {
        return $this->executionContextRegistry;
    }

    private function getPublishedWorkflowsIds(): array
    {
        $workflowsModel = new WorkflowsModel();
        $workflows = $workflowsModel->getPublishedWorkflowsIds();

        return $workflows;
    }

    private function getAllStepTypes(): array
    {
        return $this->stepTypesModel->getAllStepTypesByType();
    }

    /**
     * Determines the execution environment of the workflow engine.
     *
     * @return string The environment context (cli, admin, cron, ajax, or frontend)
     */
    private function getEngineExecutionEnvironment(): string
    {
        if (defined('WP_CLI')) {
            return 'cli';
        }

        if (is_admin()) {
            return 'admin';
        }

        if (defined('DOING_CRON')) {
            return 'cron';
        }

        if (defined('DOING_AJAX')) {
            return 'ajax';
        }

        if (defined('REST_REQUEST')) {
            return 'rest';
        }

        return 'frontend';
    }

    private function getCurrentUser(): \WP_User
    {
        $currentUser = wp_get_current_user();

        return $currentUser;
    }
}
