<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostWorkflowEnable;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\PostModelInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\IntegerResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\PostResolver;
use WP_Post;

class PostModel implements PostModelInterface
{
    public const META_KEY_WORKFLOW_MANUALLY_TRIGGERED = '_pp_workflow_manually_triggered';

    /**
     * @var WP_Post|null
     */
    private $post;

    /**
     * @var array<int, array<string, mixed>>|null
     */
    private $cachedScheduledActionsRows = null;

    /**
     * @var int|null
     */
    private $cachedScheduledActionsWorkflowId = null;

    public function load(int $id): bool
    {
        $this->reset();

        $post = get_post($id);

        if (! $post instanceof WP_Post) {
            return false;
        }

        $this->post = $post;

        return true;
    }

    private function reset(): void
    {
        $this->post = null;
        $this->cachedScheduledActionsRows = null;
        $this->cachedScheduledActionsWorkflowId = null;
    }

    public function getId(): int
    {
        if (! $this->isPostLoaded()) {
            return 0;
        }

        return $this->post->ID;
    }

    public function getTitle(): string
    {
        if (! $this->isPostLoaded()) {
            return '';
        }

        return $this->post->post_title;
    }

    public function getValidWorkflowsWithManualTrigger(int $postId): array
    {
        $postModel = new PostModel();
        if (! $postModel->load($postId)) {
            return [];
        }

        $postObject = $postModel->getPostObject();
        if (! ($postObject instanceof WP_Post)) {
            return [];
        }

        $workflowsModel = new WorkflowsModel();
        $workflows = $workflowsModel->getPublishedWorkflowsWithManualTrigger();

        $container = Container::getInstance();
        // TODO: Inject this
        $postQueryValidatorFactory = $container->get(ServicesAbstract::INPUT_VALIDATOR_POST_QUERY_FACTORY);
        $workflowEngine = $container->get(ServicesAbstract::WORKFLOW_ENGINE);
        $executionContextRegistry = $container->get(ServicesAbstract::EXECUTION_CONTEXT_REGISTRY);
        $hooks = $container->get(ServicesAbstract::HOOKS);
        $expirablePostModelFactory = $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);

        $validatedWorkflows = [];

        foreach ($workflows as $workflow) {
            if (! isset($workflow['workflowId'])) {
                continue;
            }

            $workflowId = $workflow['workflowId'];

            $workflowModel = new WorkflowModel();
            if (! $workflowModel->load($workflowId)) {
                continue;
            }

            // Prepare for a new context
            $workflowExecutionId = $workflowEngine->generateUniqueId();
            $postQueryValidator = $postQueryValidatorFactory($workflowExecutionId);

            $workflowEngine->prepareExecutionContextForWorkflow(
                $workflowExecutionId,
                $workflowModel
            );

            // Validate the trigger's post query
            $triggers = $workflowModel->getTriggerNodes();
            foreach ($triggers as $triggerStep) {
                if (! isset($triggerStep['data']['name'])) {
                    continue;
                }

                if (! isset($triggerStep['data']['slug'])) {
                    continue;
                }

                $triggerName = $triggerStep['data']['name'];

                if ($triggerName !== OnPostWorkflowEnable::getNodeTypeName()) {
                    continue;
                }

                $workflowEngine->prepareExecutionContextForTrigger(
                    $workflowExecutionId,
                    $triggerStep
                );

                $executionContext = $executionContextRegistry->getExecutionContext($workflowExecutionId);
                $executionContext->setVariable($triggerStep['data']['slug'], [
                    'post' => new PostResolver($postObject, $hooks, '', $expirablePostModelFactory),
                    'postId' => new IntegerResolver($postId)
                ]);

                if (! $postQueryValidator->validate(['post' => $postObject, 'node' => $triggerStep])) {
                    continue;
                }

                $validatedWorkflows[] = $workflow;
            }
        }

        return $validatedWorkflows;
    }

    public function getManuallyEnabledWorkflows(): array
    {
        if (! $this->isPostLoaded()) {
            return [];
        }

        $selectedWorkflowIds = (array) get_post_meta($this->post->ID, self::META_KEY_WORKFLOW_MANUALLY_TRIGGERED, false);
        $selectedWorkflowIds = array_map('intval', $selectedWorkflowIds);

        return $selectedWorkflowIds;
    }

    public function setManuallyEnabledWorkflows(array $workflowIds): void
    {
        if (! $this->isPostLoaded()) {
            return;
        }

        $currentWorkflowIds = $this->getManuallyEnabledWorkflows();

        $workflowIds = $this->sanitizeIntArrayOfUniqueValues($workflowIds);

        $workflowsToDisable = array_diff($currentWorkflowIds, $workflowIds);

        foreach ($workflowsToDisable as $workflowId) {
            $this->removeScheduledActionsFromDisabledWorkflows($workflowId);
        }

        delete_post_meta($this->post->ID, self::META_KEY_WORKFLOW_MANUALLY_TRIGGERED);

        foreach ($workflowIds as $workflowId) {
            add_post_meta($this->post->ID, self::META_KEY_WORKFLOW_MANUALLY_TRIGGERED, $workflowId, false);
        }
    }

    public function addManuallyEnabledWorkflow(int $workflowId): void
    {
        if (! $this->isPostLoaded()) {
            return;
        }

        $workflowIds = $this->getManuallyEnabledWorkflows();
        $workflowIds[] = $workflowId;

        $workflowIds = $this->sanitizeIntArrayOfUniqueValues($workflowIds);

        $this->setManuallyEnabledWorkflows($workflowIds);
    }

    public function removeManuallyEnabledWorkflow(int $workflowId): void
    {
        if (! $this->isPostLoaded()) {
            return;
        }

        $workflowIds = $this->getManuallyEnabledWorkflows();

        $workflowIds = array_filter($workflowIds, function ($id) use ($workflowId) {
            return $id !== $workflowId;
        });

        $workflowIds = $this->sanitizeIntArrayOfUniqueValues($workflowIds);

        $this->setManuallyEnabledWorkflows($workflowIds);
    }

    private function removeScheduledActionsFromDisabledWorkflows(int $workflowId): void
    {
        if (! $this->isPostLoaded()) {
            return;
        }

        // Check if the workflow has a scheduled action for this post
        $scheduledActionsModel = new ScheduledActionsModel();
        $scheduledActionsModel->cancelByWorkflowAndPostId($workflowId, $this->post->ID);
    }

    public function getManuallyEnabledWorkflowsSchedule(int $workflowId): array
    {
        if (! $this->isPostLoaded()) {
            return [];
        }

        global $wpdb;

        $workflowModel = new WorkflowModel();

        if (! $workflowModel->load($workflowId)) {
            return [];
        }

        // FIXME: Use dependency injection
        $stepTypesModel = Container::getInstance()->get(ServicesAbstract::STEP_TYPES_MODEL);
        $allStepTypes = $stepTypesModel->getAllStepTypesIndexedByName();

        if ($this->cachedScheduledActionsWorkflowId !== $workflowId || $this->cachedScheduledActionsRows === null) {
            $query = "SELECT aa.scheduled_date_gmt, aa.args, aa.extended_args, aa.action_id
                FROM {$wpdb->prefix}actionscheduler_actions AS aa
                INNER JOIN {$wpdb->prefix}ppfuture_workflow_scheduled_steps AS ss ON ss.action_id = aa.action_id
                WHERE ss.post_id = %d
                AND ss.workflow_id = %d
                AND aa.status = 'pending'
                AND aa.hook = %s
            ";
            $query = $wpdb->prepare(
                $query, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
                $this->post->ID,
                $workflowId,
                HooksAbstract::ACTION_SCHEDULED_STEP_EXECUTE
            );

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
            $results = $wpdb->get_results($query, ARRAY_A);
            $this->cachedScheduledActionsRows = is_array($results) ? $results : [];
            $this->cachedScheduledActionsWorkflowId = $workflowId;
        }

        $actionsForWorkflows = $this->cachedScheduledActionsRows;

        if (empty($actionsForWorkflows)) {
            return [];
        }

        $schedule = [];

        foreach ($actionsForWorkflows as $action) {
            $row = $this->buildScheduleRowForManualWorkflowAction(
                $action,
                $workflowModel,
                $allStepTypes,
                $workflowId,
                $this->post->ID
            );

            if ($row !== null) {
                $schedule[] = $row;
            }
        }

        return $schedule;
    }

    /**
     * Build one schedule row for a pending manual-workflow action, or null when the action should be skipped.
     *
     * @param array<string, mixed> $action Row from the scheduled actions query.
     * @param WorkflowModel $workflowModel Loaded workflow model for the current workflow.
     * @param array<string, mixed> $allStepTypes Step types indexed by name (from StepTypesModel).
     * @param int $workflowId Workflow identifier.
     * @param int $postId Post ID this schedule is scoped to (strict match against action args).
     *
     * @return array<string, mixed>|null Row with workflowId, workflowTitle, timestamp, nextStep; null to skip.
     */
    private function buildScheduleRowForManualWorkflowAction(
        array $action,
        WorkflowModel $workflowModel,
        array $allStepTypes,
        int $workflowId,
        int $postId
    ): ?array {
        if (! isset($action['action_id'])) {
            return null;
        }

        $actionId = $action['action_id'];

        $scheduledStepModel = new WorkflowScheduledStepModel();
        $scheduledStepModel->loadByActionId($actionId);

        $args = $scheduledStepModel->getArgs();

        if (! isset($args['runtimeVariables']['global']['trigger']['value']['slug'])) {
            return null;
        }

        $triggerSlug = $args['runtimeVariables']['global']['trigger']['value']['slug'];

        if (! isset($args['runtimeVariables'][$triggerSlug]['postId'])) {
            return null;
        }

        if (! isset($args['runtimeVariables'][$triggerSlug]['postId']['value'])) {
            return null;
        }

        $postIdFromArgs = $args['runtimeVariables'][$triggerSlug]['postId']['value'];

        if ((int) $postIdFromArgs !== $postId) {
            return null;
        }

        if (! isset($args['step']['nodeId'])) {
            return null;
        }

        $stepRoutineTree = $workflowModel->getPartialRoutineTreeFromNodeId($args['step']['nodeId']);

        if (empty($stepRoutineTree) || empty($stepRoutineTree['next'])) {
            return null;
        }

        if (! isset($stepRoutineTree['next']['output'][0]['node'])) {
            return null;
        }

        $nextStep = $stepRoutineTree['next']['output'][0]['node'];

        if (empty($nextStep)) {
            return null;
        }

        return [
            'workflowId' => $workflowId,
            'workflowTitle' => $workflowModel->getManualSelectionLabel(),
            'timestamp' => isset($action['scheduled_date_gmt']) ? strtotime($action['scheduled_date_gmt']) : 0,
            'nextStep' => $this->getNextStepLabel($nextStep, $allStepTypes),
        ];
    }

    /**
     * @return WP_Post|null
     */
    public function getPostObject()
    {
        return $this->post;
    }

    private function isPostLoaded(): bool
    {
        return $this->post instanceof WP_Post;
    }

    private function getNextStepLabel(array $nextStep, array $allStepTypes): string
    {
        if (isset($nextStep['data']['label'])) {
            return (string) $nextStep['data']['label'];
        }

        if (! isset($nextStep['data']['name'])) {
            return __('Unknown Step', 'post-expirator');
        }

        if (! isset($allStepTypes[$nextStep['data']['name']])) {
            return __('Unknown Step', 'post-expirator');
        }

        return (string) $allStepTypes[$nextStep['data']['name']]->getLabel();
    }

    private function sanitizeIntArrayOfUniqueValues(array $array): array
    {
        if (empty($array)) {
            return [];
        }

        $array = array_map('intval', $array);
        $array = array_unique($array);

        $array = array_filter($array, function ($value) {
            return $value > 0;
        });

        return $array;
    }
}
