<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypesModelInterface;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\AddPostMeta;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\AddPostTerm;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\AppendDebugLog;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\ChangePostStatus;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\Conditional;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\DeactivatePostWorkflow;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\DeletePost;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\DeletePostMeta;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\DoAction;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\UserInteraction;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\QueryPosts;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\RemovePostTerm;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\ScheduleDelay;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\SendInSiteNotification;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\SendEmail;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\SendRay;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\SetPostTerm;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\StickPost;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\UnstickPost;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\UpdatePost;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\UpdatePostMeta;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\DuplicatePost;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnAdminInit;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnCustomAction;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnTermsAdded;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnInit;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnLegacyActionTrigger;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostAuthorChange;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostMetaChange;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostPublish;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostRowAction;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostSave;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostSchedule;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostStatusChange;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostUpdate;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostWorkflowEnable;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnSchedule;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnUserRoleChange;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;

class StepTypesModel implements StepTypesModelInterface
{
    public const STEP_TYPE_ACTION = "action";

    public const STEP_TYPE_TRIGGER = "trigger";

    public const STEP_TYPE_ADVANCED = "advanced";

    public const STEP_VERSION = "1";

    public const DEFAULT_ICON_BACKGROUND = "#ffffff";

    public const DEFAULT_ICON_FOREGROUND = "#1e1e1e";

    private $hooks;

    private $categories = [];

    private $triggerSteps = [];

    private $actionSteps = [];

    private $advancedSteps = [];

    /**
     * @var SettingsFacade
     */
    private $settingsFacade;

    public function __construct(HooksFacade $hooks, SettingsFacade $settingsFacade)
    {
        $this->hooks = $hooks;
        $this->settingsFacade = $settingsFacade;
    }

    private function getDefaultCategories()
    {
        return [
            [
                "name" => "post",
                "label" => __("Post", "post-expirator"),
                "icon" => [
                    "src" => "media-document",
                    "background" => self::DEFAULT_ICON_BACKGROUND,
                    "foreground" => self::DEFAULT_ICON_FOREGROUND,
                ],
            ],
            [
                "name" => "future",
                "label" => __("PublishPress Future", "post-expirator"),
                "icon" => [
                    "src" => "media-document",
                    "background" => self::DEFAULT_ICON_BACKGROUND,
                    "foreground" => self::DEFAULT_ICON_FOREGROUND,
                ],
            ],
            [
                "name" => "user",
                "label" => __("User", "post-expirator"),
                "icon" => [
                    "src" => "media-document",
                    "background" => self::DEFAULT_ICON_BACKGROUND,
                    "foreground" => self::DEFAULT_ICON_FOREGROUND,
                ],
            ],
            [
                "name" => "site",
                "label" => __("Site", "post-expirator"),
                "icon" => [
                    "src" => "media-document",
                    "background" => self::DEFAULT_ICON_BACKGROUND,
                    "foreground" => self::DEFAULT_ICON_FOREGROUND,
                ],
            ],
            [
                "name" => "flow",
                "label" => __("Flow Control", "post-expirator"),
                "icon" => [
                    "src" => "route",
                    "background" => self::DEFAULT_ICON_BACKGROUND,
                    "foreground" => self::DEFAULT_ICON_FOREGROUND,
                ],
            ],
            [
                "name" => "async",
                "label" => __("Asynchronous", "post-expirator"),
                "icon" => [
                    "src" => "media-document",
                    "background" => self::DEFAULT_ICON_BACKGROUND,
                    "foreground" => self::DEFAULT_ICON_FOREGROUND,
                ],
            ],
            [
                "name" => "debug",
                "label" => __("Debug", "post-expirator"),
                "icon" => [
                    "src" => "debug",
                    "background" => self::DEFAULT_ICON_BACKGROUND,
                    "foreground" => self::DEFAULT_ICON_FOREGROUND,
                ],
            ],
            [
                "name" => "db-query",
                "label" => __("Data Query", "post-expirator"),
                "icon" => [
                    "src" => "db-query",
                    "background" => self::DEFAULT_ICON_BACKGROUND,
                    "foreground" => self::DEFAULT_ICON_FOREGROUND,
                ],
            ],
            [
                "name" => "messages",
                "label" => __("Messages", "post-expirator"),
                "icon" => [
                    "src" => "messages",
                    "background" => self::DEFAULT_ICON_BACKGROUND,
                    "foreground" => self::DEFAULT_ICON_FOREGROUND,
                ],

            ]
        ];
    }

    public function convertInstancesToArray($instances, $type): array
    {
        $instancesCopy = $instances;

        /**
         * @var StepTypeInterface $instance
         */
        foreach ($instancesCopy as &$instance) {
            $instanceClass = get_class($instance);

            $instance = $this->applyDefaultParams(
                [
                    "type" => $instance->getReactFlowNodeType(),
                    "elementaryType" => $instance->getElementaryType(),
                    "name" => $instanceClass::getNodeTypeName(),
                    "label" => $instance->getLabel(),
                    "description" => $instance->getDescription(),
                    "category" => $instance->getCategory(),
                    "frecency" => $instance->getFrecency(),
                    "icon" => [
                        "src" => $instance->getIcon(),
                        "background" => self::DEFAULT_ICON_BACKGROUND,
                        "foreground" => self::DEFAULT_ICON_FOREGROUND,
                    ],
                    "settingsSchema" => $instance->getSettingsSchema(),
                    "validationSchema" => $instance->getValidationSchema(),
                    "className" => $instance->getCSSClass(),
                    "version" => $instance->getVersion(),
                    "outputSchema" => $instance->getOutputSchema(),
                    "handleSchema" => $instance->getHandleSchema(),
                    "baseSlug" => $instance->getBaseSlug(),
                    "isProFeature" => $instance->isProFeature(),
                    "stepScopedVariablesSchema" => $instance->getStepScopedVariablesSchema(),
                ],
                $type
            );
        }

        return $instancesCopy;
    }

    private function getDefaultTriggerSteps()
    {
        $nodesInstances = [
            OnPostSave::getNodeTypeName() => new OnPostSave(),
            OnPostUpdate::getNodeTypeName() => new OnPostUpdate(),
            OnPostPublish::getNodeTypeName() => new OnPostPublish(),
            OnPostSchedule::getNodeTypeName() => new OnPostSchedule(),
            OnPostStatusChange::getNodeTypeName() => new OnPostStatusChange(),
            OnPostWorkflowEnable::getNodeTypeName() => new OnPostWorkflowEnable(),
            OnLegacyActionTrigger::getNodeTypeName() => new OnLegacyActionTrigger($this->hooks),
            OnSchedule::getNodeTypeName() => new OnSchedule(),
            OnPostMetaChange::getNodeTypeName() => new OnPostMetaChange(),
            OnPostAuthorChange::getNodeTypeName() => new OnPostAuthorChange(),
            OnPostRowAction::getNodeTypeName() => new OnPostRowAction(),
            OnUserRoleChange::getNodeTypeName() => new OnUserRoleChange(),
            OnTermsAdded::getNodeTypeName() => new OnTermsAdded(),
            OnCustomAction::getNodeTypeName() => new OnCustomAction(),
        ];

        if ($this->settingsFacade->getExperimentalFeaturesStatus()) {
            $nodesInstances[OnInit::getNodeTypeName()] = new OnInit();
            $nodesInstances[OnAdminInit::getNodeTypeName()] = new OnAdminInit();
        }

        return $nodesInstances;
    }

    private function getDefaultActionSteps()
    {
        $nodesInstances = [
            DeletePost::getNodeTypeName() => new DeletePost(),
            StickPost::getNodeTypeName() => new StickPost(),
            UnstickPost::getNodeTypeName() => new UnstickPost(),
            AddPostTerm::getNodeTypeName() => new AddPostTerm(),
            SetPostTerm::getNodeTypeName() => new SetPostTerm(),
            RemovePostTerm::getNodeTypeName() => new RemovePostTerm(),
            ChangePostStatus::getNodeTypeName() => new ChangePostStatus(),
            SendEmail::getNodeTypeName() => new SendEmail(),
            DeactivatePostWorkflow::getNodeTypeName() => new DeactivatePostWorkflow(),
            AddPostMeta::getNodeTypeName() => new AddPostMeta(),
            DeletePostMeta::getNodeTypeName() => new DeletePostMeta(),
            UpdatePostMeta::getNodeTypeName() => new UpdatePostMeta(),
            UpdatePost::getNodeTypeName() => new UpdatePost(),
            SendInSiteNotification::getNodeTypeName() => new SendInSiteNotification(),
            DuplicatePost::getNodeTypeName() => new DuplicatePost(),
        ];

        return $nodesInstances;
    }

    private function getDefaultAdvancedSteps()
    {
        $nodesInstances = [
            ScheduleDelay::getNodeTypeName() => new ScheduleDelay(),
            QueryPosts::getNodeTypeName() => new QueryPosts(),
            Conditional::getNodeTypeName() => new Conditional(),
            AppendDebugLog::getNodeTypeName() => new AppendDebugLog(),
            DoAction::getNodeTypeName() => new DoAction(),
            UserInteraction::getNodeTypeName() => new UserInteraction(),
        ];

        if (function_exists('ray')) {
            $nodesInstances[SendRay::getNodeTypeName()] = new SendRay();
        }

        return $nodesInstances;
    }

    private function applyDefaultParams(array $step, string $type): array
    {
        $normalized = [];

        $defaultStepAttributes = [
            "id" => "",
            "type" => $type,
            "elementaryType" => "",
            "name" => "",
            "label" => "",
            "description" => "",
            "baseSlug" => "step",
            "initiatlAttributes" => [],
            "category" => "",
            "disabled" => false,
            "isDisabled" => false,
            "frecency" => 1,
            "icon" => [
                "src" => "media-document",
                "background" => "#ffffff",
                "foreground" => "#1e1e1e",
            ],
            "version" => self::STEP_VERSION,
            "settingsSchema" => [],
            "outputSchema" => [],
            "className" => "react-flow__node-genericNode",
            "handleSchema" => [],
            "isProFeature" => false,
        ];

        return array_merge($defaultStepAttributes, $step);
    }

    public function getTriggerSteps(): array
    {
        if (!empty($this->triggerSteps)) {
            return $this->triggerSteps;
        }

        $this->triggerSteps = $this->hooks->applyFilters(
            HooksAbstract::FILTER_WORKFLOW_TRIGGER_STEPS,
            $this->getDefaultTriggerSteps()
        );

        /** @deprecated 4.3.1 */
        $this->triggerSteps = $this->hooks->applyFilters(
            HooksAbstract::FILTER_WORKFLOW_TRIGGER_NODES,
            $this->triggerSteps
        );


        return $this->triggerSteps;
    }

    public function getActionSteps(): array
    {
        if (!empty($this->actionSteps)) {
            return $this->actionSteps;
        }

        $this->actionSteps = $this->hooks->applyFilters(
            HooksAbstract::FILTER_WORKFLOW_ACTION_STEPS,
            $this->getDefaultActionSteps()
        );

        /** @deprecated 4.3.1 */
        $this->actionSteps = $this->hooks->applyFilters(
            HooksAbstract::FILTER_WORKFLOW_ACTION_NODES,
            $this->actionSteps
        );

        return $this->actionSteps;
    }

    public function getAdvancedSteps(): array
    {
        if (!empty($this->advancedSteps)) {
            return $this->advancedSteps;
        }


        $this->advancedSteps = $this->hooks->applyFilters(
            HooksAbstract::FILTER_WORKFLOW_ADVANCED_STEPS,
            $this->getDefaultAdvancedSteps()
        );

        /** @deprecated 4.3.1 */
        $this->advancedSteps = $this->hooks->applyFilters(
            HooksAbstract::FILTER_WORKFLOW_ADVANCED_NODES,
            $this->advancedSteps
        );

        return $this->advancedSteps;
    }

    public function getCategories(): array
    {
        if (!empty($this->categories)) {
            return $this->categories;
        }

        $this->categories = $this->hooks->applyFilters(
            HooksAbstract::FILTER_WORKFLOW_STEP_CATEGORIES,
            $this->getDefaultCategories()
        );

        /** @deprecated 4.3.1 */
        $this->categories = $this->hooks->applyFilters(
            HooksAbstract::FILTER_WORKFLOW_NODE_CATEGORIES,
            $this->categories
        );

        return $this->categories;
    }

    public function getAllStepTypesIndexedByName(): array
    {
        $nodeTypes = $this->getAllStepTypes();

        $indexed = [];

        foreach ($nodeTypes as $nodeType) {
            $nodeTypeClass = get_class($nodeType);
            $indexed[$nodeTypeClass::getNodeTypeName()] = $nodeType;
        }

        return $indexed;
    }

    public function getStepType(string $name): ?StepTypeInterface
    {
        $nodes = $this->getAllStepTypesIndexedByName();

        return $nodes[$name] ?? null;
    }

    public function getStrings(): array
    {
        $nodeTypes = $this->getAllStepTypesIndexedByName();

        $strings = [];
        foreach ($nodeTypes as $nodeType) {
            $nodeTypeClass = get_class($nodeType);

            $strings[$nodeTypeClass::getNodeTypeName()] = [
                'label' => $nodeType->getLabel(),
                'description' => $nodeType->getDescription(),
            ];
        }

        return $strings;
    }

    public function getAllStepTypes(): array
    {
        return array_merge(
            $this->getTriggerSteps(),
            $this->getActionSteps(),
            $this->getAdvancedSteps()
        );
    }

    public function getAllStepTypesByType(): array
    {
        return [
            self::STEP_TYPE_ACTION => $this->getActionSteps(),
            self::STEP_TYPE_TRIGGER => $this->getTriggerSteps(),
            self::STEP_TYPE_ADVANCED => $this->getAdvancedSteps(),
        ];
    }
}
