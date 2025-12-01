<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use PublishPress\Future\Modules\Workflows\Module;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowModelInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepTypesModelInterface;
use Exception;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\HooksAbstract as WorkflowsHooksAbstract;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnLegacyActionTrigger;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostWorkflowEnable;
use WP_Post;
use WP_Query;

use function wp_json_encode;
use function get_post;

class WorkflowModel implements WorkflowModelInterface
{
    public const META_KEY_HAS_LEGACY_TRIGGER = '_pp_workflow_has_legacy_trigger';

    public const META_KEY_HAS_MANUAL_TRIGGER = '_pp_workflow_has_manual_trigger';

    public const META_KEY_DEBUG_RAY_SHOW_QUERIES = '_pp_workflow_debug_ray_show_queries';

    public const META_KEY_DEBUG_RAY_SHOW_EMAILS = '_pp_workflow_debug_ray_show_emails';

    public const META_KEY_DEBUG_RAY_SHOW_WORDPRESS_ERRORS = '_pp_workflow_debug_ray_show_wordpress_errors';

    public const META_KEY_DEBUG_RAY_SHOW_CURRENT_RUNNING_STEP = '_pp_workflow_debug_ray_show_current_running_step';

    public const STATUS_ENABLED = 'publish';

    public const STATUS_DISABLED = 'draft';

    private $post;

    /**
     * @var array
     */
    private $flow = null;

    private $hasLegacyActionTrigger = null;

    private $hasManualSelectionTrigger = null;

    private $allStepTypes = null;

    private $debugRayShowQueries = null;

    private $debugRayShowEmails = null;

    private $debugRayShowWordPressErrors = null;

    private $debugRayShowCurrentRunningStep = null;

    /**
     * @var StepTypesModelInterface
     */
    private $stepTypesModel;

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    public function __construct()
    {
        $container = Container::getInstance();

        // FIXME: Use dependency injection
        $this->hooks = $container->get(ServicesAbstract::HOOKS);
        $this->stepTypesModel = $container->get(ServicesAbstract::STEP_TYPES_MODEL);
        $this->logger = $container->get(ServicesAbstract::LOGGER);
        $this->settingsFacade = $container->get(ServicesAbstract::SETTINGS);
    }

    public function load(int $id): bool
    {
        $this->reset();

        $post = get_post($id);

        if ((! ($post instanceof WP_Post)) || $post->post_type !== Module::POST_TYPE_WORKFLOW) {
            return false;
        }

        $this->post = $post;

        return true;
    }

    private function reset(): void
    {
        $this->post = null;
        $this->flow = null;
        $this->hasLegacyActionTrigger = null;
        $this->hasManualSelectionTrigger = null;
        $this->allStepTypes = null;
        $this->debugRayShowEmails = null;
        $this->debugRayShowQueries = null;
        $this->debugRayShowWordPressErrors = null;
        $this->debugRayShowCurrentRunningStep = null;
    }

    private function getPostProperty(string $property)
    {
        if (empty($this->post)) {
            return null;
        }

        return $this->post->$property;
    }

    private function getPostPropertyAsInt(string $property): int
    {
        $value = $this->getPostProperty($property);

        return (int) $value;
    }

    private function getPostPropertyAsString(string $property): string
    {
        $value = $this->getPostProperty($property);

        return (string) $value;
    }

    public function getId(): int
    {
        return $this->getPostPropertyAsInt('ID');
    }

    public function getTitle(): string
    {
        if (empty($this->post)) {
            return '';
        }

        return $this->post->post_title;
    }

    public function setTitle(string $title)
    {
        if (empty($this->post)) {
            return;
        }

        $this->post->post_title = sanitize_text_field($title);
    }

    public function getDescription(): string
    {
        return $this->getPostPropertyAsString('post_excerpt');
    }

    public function setDescription(string $description)
    {
        if (empty($this->post)) {
            return;
        }

        $this->post->post_excerpt = wp_kses_post($description);
    }

    public function getStatus(): string
    {
        return $this->getPostPropertyAsString('post_status');
    }

    public function setStatus(string $status)
    {
        if (empty($this->post)) {
            return;
        }

        $this->post->post_status = sanitize_key($status);
    }

    public function publish()
    {
        if (empty($this->post)) {
            return;
        }

        $this->setStatus(self::STATUS_ENABLED);
        $this->post->post_date = current_time('mysql');
        $this->post->post_date_gmt = current_time('mysql');
        $this->save();
    }

    public function unpublish()
    {
        if (empty($this->post)) {
            return;
        }

        $this->setStatus(self::STATUS_DISABLED);
        $this->save();
    }

    public function isActive(): bool
    {
        if (empty($this->post)) {
            return false;
        }

        return $this->post->post_status === self::STATUS_ENABLED;
    }

    private function updateLegacyActionMetadata()
    {
        if (empty($this->post)) {
            return;
        }

        $this->hasLegacyActionTrigger = $this->checkHasLegacyActionTriggerInTheFlow();

        if ($this->hasLegacyActionTrigger) {
            update_post_meta($this->post->ID, self::META_KEY_HAS_LEGACY_TRIGGER, '1');
        } else {
            delete_post_meta($this->post->ID, self::META_KEY_HAS_LEGACY_TRIGGER);
        }
    }

    private function updateManualSelectionMetadata()
    {
        $this->hasManualSelectionTrigger = $this->checkHasManualSelectionTriggerInTheFlow();

        if ($this->hasManualSelectionTrigger) {
            update_post_meta($this->post->ID, self::META_KEY_HAS_MANUAL_TRIGGER, '1');
        } else {
            delete_post_meta($this->post->ID, self::META_KEY_HAS_MANUAL_TRIGGER);
        }
    }

    private function updateDebugRayMetadata()
    {
        update_post_meta(
            $this->post->ID,
            self::META_KEY_DEBUG_RAY_SHOW_QUERIES,
            $this->debugRayShowQueries ? '1' : '0'
        );
        update_post_meta(
            $this->post->ID,
            self::META_KEY_DEBUG_RAY_SHOW_EMAILS,
            $this->debugRayShowEmails ? '1' : '0'
        );
        update_post_meta(
            $this->post->ID,
            self::META_KEY_DEBUG_RAY_SHOW_WORDPRESS_ERRORS,
            $this->debugRayShowWordPressErrors ? '1' : '0'
        );
        update_post_meta(
            $this->post->ID,
            self::META_KEY_DEBUG_RAY_SHOW_CURRENT_RUNNING_STEP,
            $this->debugRayShowCurrentRunningStep ? '1' : '0'
        );
    }

    public function getModifiedAt(): string
    {
        return $this->getPostPropertyAsString('post_modified');
    }

    public function save()
    {
        if (empty($this->post)) {
            return;
        }

        wp_update_post($this->post);

        $this->updateLegacyActionMetadata();
        $this->updateManualSelectionMetadata();
        $this->updateDebugRayMetadata();
    }

    public function delete()
    {
        if (empty($this->post)) {
            return;
        }

        wp_delete_post($this->post->ID);
        $this->reset();

        /**
         * @param int $workflowId
         */
        $this->hooks->doAction(WorkflowsHooksAbstract::ACTION_WORKFLOW_DELETED, $this->post->ID);
    }

    private function getAllNodeTypesByType(): array
    {
        if (empty($this->post)) {
            return [];
        }

        if (is_null($this->allStepTypes)) {
            // Ensure the flow is updated with the latest node types
            // FIXME: Use dependency injection
            $stepTypesModel = Container::getInstance()->get(ServicesAbstract::STEP_TYPES_MODEL);
            $this->allStepTypes = $stepTypesModel->getAllStepTypesIndexedByName();
        }

        return $this->allStepTypes;
    }

    public function getFlow(bool $updateSteps = false): array
    {
        if (empty($this->post)) {
            return [];
        }

        if (empty($this->flow)) {
            $this->flow = json_decode($this->post->post_content, true);

            if (! is_array($this->flow)) {
                $this->flow = [];
            }

            if ($updateSteps) {
                if (empty($this->flow)) {
                    return $this->flow;
                }

                // Check if the nodes are updated and update them if necessary
                $nodes = $this->flow['nodes'] ?? [];
                $nodesUpdated = false;
                foreach ($nodes as &$node) {
                    if (! $this->isNodeUpdated($node)) {
                        $node = $this->updateStep($node);
                        $nodesUpdated = true;
                    }
                }
                if ($nodesUpdated) {
                    $this->flow['nodes'] = $nodes;
                }
            }
        }

        return $this->flow;
    }

    private function getStepTypeByname(string $name)
    {
        $stepTypes = $this->getAllNodeTypesByType();

        $stepType = $stepTypes[$name] ?? null;

        if (is_null($stepType)) {
            throw new Exception('Node type not found: ' . esc_html($name));
        }

        return $stepType;
    }

    private function isNodeUpdated(array $node): bool
    {
        $stepType = $this->getStepTypeByname($node['data']['name'] ?? '');
        $stepVersion = $this->getStepVersion($node);

        if (! $stepType) {
            return false;
        }

        return $stepVersion === $stepType->getVersion();
    }

    private function getStepVersion(array $node): int
    {
        return (int)($node['data']['version'] ?? 0);
    }

    private function getUnstranslatedString(string $string): string
    {
        // Force the WP locale to en_US to get the untranslated string
        $currentLocale = get_locale();
        switch_to_locale('en_US');

        $untranslatedString = __($string, 'post-expirator');

        // Restore the original locale
        switch_to_locale($currentLocale);

        return $untranslatedString;
    }

    private function updateStep(array $node): array
    {
        $stepType = $this->getStepTypeByname($node['data']['name']);
        $stepVersion = $this->getStepVersion($node);

        if ($stepType->getVersion() < $stepVersion) {
            // TODO: What to do when the node type is downgraded? Should we have a check in the version of the builder?
            return $node;
        }
        // phpcs:ignore Squiz.PHP.CommentedOutCode.Found
        // if ($stepType->getVersion() > $stepVersion) {
        //     // Update the version
        //     $node['data']['version'] = $stepType->getVersion();
        // }

        return $node;
    }

    public function setFlow(array $flow)
    {
        // Update the editor version in the flow
        $flow['editorVersion'] = PUBLISHPRESS_FUTURE_VERSION;

        $this->flow = $flow;
        $this->post->post_content = wp_json_encode($this->flow);
    }

    public function createNew($reuseAutoDraft = true): int
    {
        $this->reset();

        $id = 0;

        if ($reuseAutoDraft) {
            // Get the first auto-draft workflow created by the user in the current session
            $query = new WP_Query([
                'post_type' => Module::POST_TYPE_WORKFLOW,
                'post_status' => 'auto-draft',
                'author' => get_current_user_id(),
                'posts_per_page' => 1,
            ]);

            if ($query->have_posts()) {
                $query->the_post();

                $id = get_the_ID();
            }
        }

        if (empty($id)) {
            $this->post = [
                'post_title' => __('New Workflow', 'post-expirator'),
                'post_status' => 'auto-draft',
                'post_type' => Module::POST_TYPE_WORKFLOW,
            ];

            $id = wp_insert_post($this->post);
        }

        $this->load($id);

        return $id;
    }

    public function createCopy(): WorkflowModelInterface
    {
        // Create a new workflow
        $newWorkflow = new WorkflowModel();
        $newWorkflow->createNew(false);

        // Copy properties  of current workflow to new workflow
        $newWorkflow->setTitle(sprintf(__('%s #2', 'post-expirator'), $this->getTitle()));
        $newWorkflow->setDescription($this->getDescription());
        $newWorkflow->setFlow($this->getFlow());

        // Set status to draft
        $newWorkflow->setStatus(self::STATUS_DISABLED);

        // Copy debug settings of current workflow to new workflow
        $newWorkflow->setDebugRayShowQueries($this->isDebugRayShowQueriesEnabled());
        $newWorkflow->setDebugRayShowEmails($this->isDebugRayShowEmailsEnabled());
        $newWorkflow->setDebugRayShowWordPressErrors($this->isDebugRayShowWordPressErrorsEnabled());
        $newWorkflow->setDebugRayShowCurrentRunningStep($this->isDebugRayShowCurrentRunningStepEnabled());

        // Save the new workflow
        $newWorkflow->save();

        return $newWorkflow;
    }

    private function getImageDimensionsBySize($size)
    {
        $sizes = [
            'full' => [882, 1024],
            'large' => [768, 892],
            'medium' => [258, 300],
            'thumbnail' => [150, 150],
        ];

        return $sizes[$size] ?? $sizes['full'];
    }

    public function getTriggerNodes(): array
    {
        if (empty($this->post)) {
            return [];
        }

        $abstractFlow = $this->getFlow();

        if (empty($abstractFlow)) {
            return [];
        }

        // Build a list of trigger nodes
        $triggers = [];
        foreach ($abstractFlow['nodes'] as $node) {
            if ($node['data']['elementaryType'] === StepTypesModel::STEP_TYPE_TRIGGER) {
                $triggers[] = $node;
            }
        }

        if (empty($triggers)) {
            return [];
        }

        return $triggers;
    }

    public function getEdges(): array
    {
        if (empty($this->post)) {
            return [];
        }

        $abstractFlow = $this->getFlow();

        if (empty($abstractFlow)) {
            return [];
        }

        return $abstractFlow['edges'] ?? [];
    }

    public function getRoutineTree(array $stepTypes): array
    {
        if (empty($this->post)) {
            return [];
        }

        $abstractFlow = $this->getFlow();

        if (empty($abstractFlow)) {
            return [];
        }

        $edges = $abstractFlow['edges'] ?? [];
        $nodes = $abstractFlow['nodes'] ?? [];

        $nodesById = [];
        foreach ($nodes as $node) {
            $nodesById[$node['id']] = $node;
        }

        $workflowTriggers = $this->getTriggerNodes();

        // Build the abstract routine tree for each trigger node
        $routineTree = [];
        foreach ($workflowTriggers as $triggerNode) {
            $routineTree[$triggerNode['id']] = $this->getRoutineNodesTree(
                $edges,
                $nodesById,
                $triggerNode['id'],
                $stepTypes
            );
        }

        return $routineTree;
    }

    private function convertDynamicHandlesToStatic(array $handles, array $node): array
    {
        $convertedHandles = [];

        foreach ($handles as &$handle) {
            if (! isset($handle['type']) || substr($handle['type'], 0, 12) !== '__dynamic__:') {
                $convertedHandles[] = $handle;
                continue;
            }

            // Get the option data
            $settingName = substr($handle['type'], 12);

            $optionData = $node['data']['settings'][$settingName] ?? [];

            foreach ($optionData as $option) {
                $convertedHandles[] = [
                    'id' => $option['name'],
                    'label' => $option['label'],
                ];
            }
        }

        return $convertedHandles;
    }

    private function getRoutineNodesTree($edges, $nodes, $sourceNodeId, $stepTypes, $edgeId = null)
    {
        $node = $nodes[$sourceNodeId];
        $elementaryType = $node['data']['elementaryType'] ?? null;
        $stepName = $node['data']['name'] ?? null;
        $stepTypeInstance = $stepTypes[$elementaryType][$stepName] ?? null;

        if (is_null($stepTypeInstance)) {
            $this->logger->error(
                sprintf(
                    'Step type not found. Workflow: %1$d; ElementaryType: %2$s; StepName: %3$s; SourceNodeId: %4$s',
                    $this->post->ID,
                    $elementaryType,
                    $stepName,
                    $sourceNodeId
                )
            );

            return [];
        }
        $handleSchema = $stepTypeInstance->getHandleSchema();

        $tree = ['node' => $node,];

        if ($edgeId) {
            $tree['edgeId'] = $edgeId;
        }

        $tree['next'] = [];

        // Convert dynamic handles to static handles based on the node settings
        $handleSchema['source'] = $this->convertDynamicHandlesToStatic($handleSchema['source'] ?? [], $node);

        foreach ($handleSchema['source'] as $handle) {
            foreach ($edges as $edge) {
                if ($edge['source'] === $sourceNodeId && $edge['sourceHandle'] === $handle['id']) {
                    if (! isset($tree['next'][$handle['id']])) {
                        $tree['next'][$handle['id']] = [];
                    }
                    $tree['next'][$handle['id']][] = $this->getRoutineNodesTree(
                        $edges,
                        $nodes,
                        $edge['target'],
                        $stepTypes,
                        $edge['id']
                    );
                }
            }
        }

        return $tree;
    }

    private function checkHasLegacyActionTriggerInTheFlow(): bool
    {
        return $this->checkHasTriggerInTheFlow(OnLegacyActionTrigger::getNodeTypeName());
    }

    private function checkHasManualSelectionTriggerInTheFlow(): bool
    {
        return $this->checkHasTriggerInTheFlow(OnPostWorkflowEnable::getNodeTypeName());
    }

    private function checkHasTriggerInTheFlow(string $triggerName): bool
    {
        $workflowTriggers = $this->getTriggerNodes();

        foreach ($workflowTriggers as $triggerNode) {
            if (
                $triggerNode['data']['elementaryType'] === StepTypesModel::STEP_TYPE_TRIGGER
                && $triggerNode['data']['name'] === $triggerName
            ) {
                return true;
            }
        }

        return false;
    }

    public function hasLegacyActionTrigger(): bool
    {
        if (empty($this->post)) {
            return false;
        }

        if (is_null($this->hasLegacyActionTrigger)) {
            $this->updateLegacyActionMetadata();
        }

        return get_post_meta($this->post->ID, self::META_KEY_HAS_LEGACY_TRIGGER, true) === '1';
    }

    public function setDebugRayShowQueries(bool $debugRayShowQueries)
    {
        $this->debugRayShowQueries = $debugRayShowQueries;
    }

    public function setDebugRayShowEmails(bool $debugRayShowEmails)
    {
        $this->debugRayShowEmails = $debugRayShowEmails;
    }

    public function setDebugRayShowWordPressErrors(bool $debugRayShowWordPressErrors)
    {
        $this->debugRayShowWordPressErrors = $debugRayShowWordPressErrors;
    }

    public function setDebugRayShowCurrentRunningStep(bool $debugRayShowCurrentRunningStep)
    {
        $this->debugRayShowCurrentRunningStep = $debugRayShowCurrentRunningStep;
    }

    public function isDebugRayShowQueriesEnabled(): bool
    {
        if (empty($this->post)) {
            return false;
        }

        if (null === $this->debugRayShowQueries) {
            $this->debugRayShowQueries = get_post_meta(
                $this->post->ID,
                self::META_KEY_DEBUG_RAY_SHOW_QUERIES,
                true
            ) === '1';
        }

        return $this->debugRayShowQueries;
    }

    public function isDebugRayShowEmailsEnabled(): bool
    {
        if (empty($this->post)) {
            return false;
        }

        if (null === $this->debugRayShowEmails) {
            $this->debugRayShowEmails = get_post_meta(
                $this->post->ID,
                self::META_KEY_DEBUG_RAY_SHOW_EMAILS,
                true
            ) === '1';
        }

        return $this->debugRayShowEmails;
    }

    public function isDebugRayShowWordPressErrorsEnabled(): bool
    {
        if (empty($this->post)) {
            return false;
        }

        if (null === $this->debugRayShowWordPressErrors) {
            $this->debugRayShowWordPressErrors = get_post_meta(
                $this->post->ID,
                self::META_KEY_DEBUG_RAY_SHOW_WORDPRESS_ERRORS,
                true
            ) === '1';
        }

        return $this->debugRayShowWordPressErrors;
    }

    public function isDebugRayShowCurrentRunningStepEnabled(): bool
    {
        if (empty($this->post)) {
            return false;
        }

        if (null === $this->debugRayShowCurrentRunningStep) {
            $this->debugRayShowCurrentRunningStep = get_post_meta(
                $this->post->ID,
                self::META_KEY_DEBUG_RAY_SHOW_CURRENT_RUNNING_STEP,
                true
            ) === '1';
        }

        return $this->debugRayShowCurrentRunningStep;
    }

    private function getManualSelectionTrigger()
    {
        $workflowTriggers = $this->getTriggerNodes();

        foreach ($workflowTriggers as $triggerNode) {
            if (
                $triggerNode['data']['elementaryType'] === StepTypesModel::STEP_TYPE_TRIGGER
                && $triggerNode['data']['name'] === OnPostWorkflowEnable::getNodeTypeName()
            ) {
                return $triggerNode;
            }
        }

        return null;
    }

    public function getManualSelectionLabel(): string
    {
        if (empty($this->post)) {
            return '';
        }

        $manualSelectionTrigger = $this->getManualSelectionTrigger();

        if (empty($manualSelectionTrigger)) {
            return $this->getTitle();
        }

        return $manualSelectionTrigger['data']['settings']['checkboxLabel'] ?? $this->getTitle();
    }

    public function getPartialRoutineTreeFromNodeId(string $nodeId): array
    {
        if (empty($this->post)) {
            return [];
        }

        $stepTypes = $this->stepTypesModel->getAllStepTypesByType();
        $routineTree = $this->getRoutineTree($stepTypes);

        if (empty($routineTree)) {
            // TODO: Log the error
            return [];
        }

        return $this->getStepFromRoutineTreeRecursively($routineTree, $nodeId);
    }

    private function getStepFromRoutineTreeRecursively(array $routineTree, string $nodeId): array
    {
        foreach ($routineTree as $node) {
            if ($node['node']['id'] === $nodeId) {
                return $node;
            }

            if (isset($node['next'])) {
                foreach ($node['next'] as $nextNode) {
                    $step = $this->getStepFromRoutineTreeRecursively($nextNode, $nodeId);
                    if (! empty($step)) {
                        return $step;
                    }
                }
            }
        }

        return [];
    }

    public function getNodeById(string $nodeId): array
    {
        if (empty($this->post)) {
            return [];
        }

        $abstractFlow = $this->getFlow();

        if (empty($abstractFlow)) {
            return [];
        }

        $node = [];

        foreach ($abstractFlow['nodes'] as $node) {
            if ($node['id'] === $nodeId) {
                $node = $node;
                break;
            }
        }

        return $node;
    }

    public function getNodes(bool $fullNodes = false): array
    {
        if (empty($this->post)) {
            return [];
        }

        $abstractFlow = $this->getFlow();

        if (empty($abstractFlow)) {
            return [];
        }

        $nodes = [];
        foreach ($abstractFlow['nodes'] as $node) {
            if ($fullNodes) {
                $nodes[$node['data']['slug']] = $node;
            } else {
                $nodes[$node['data']['slug']] = [
                    'id' => $node['id'],
                    'type' => $node['type'],
                    'name' => $node['data']['name'],
                    'slug' => $node['data']['slug'],
                    'settings' => $node['data']['settings'],
                ];
            }
        }

        return $nodes;
    }
}
