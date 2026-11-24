<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use PublishPress\Future\Modules\Workflows\Interfaces\NodeTypesModelInterface;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Settings\SettingsFacade;

\defined('ABSPATH') || exit;

/**
 * @deprecated 4.3.1 Use StepTypesModel instead.
 */
class NodeTypesModel implements NodeTypesModelInterface
{
    public const NODE_TYPE_ACTION = "action";

    public const NODE_TYPE_TRIGGER = "trigger";

    public const NODE_TYPE_ADVANCED = "advanced";

    public const NODE_VERSION = "1";

    public const DEFAULT_ICON_BACKGROUND = "#ffffff";

    public const DEFAULT_ICON_FOREGROUND = "#1e1e1e";

    private $stepTypesModel;

    public function __construct(HooksFacade $hooks, SettingsFacade $settingsFacade)
    {
        $this->stepTypesModel = new StepTypesModel($hooks, $settingsFacade);
    }

    public function convertInstancesToArray($instances, $type): array
    {
        return $this->stepTypesModel->convertInstancesToArray($instances, $type);
    }

    public function getTriggerNodes(): array
    {
        return $this->stepTypesModel->getTriggerSteps();
    }

    public function getActionNodes(): array
    {
        return $this->stepTypesModel->getActionSteps();
    }

    public function getAdvancedNodes(): array
    {
        return $this->stepTypesModel->getAdvancedSteps();
    }

    public function getCategories(): array
    {
        return $this->stepTypesModel->getCategories();
    }

    public function getAllNodeTypesIndexedByName(): array
    {
        return $this->stepTypesModel->getAllStepTypesIndexedByName();
    }

    public function getNodeType(string $name)
    {
        return $this->stepTypesModel->getStepType($name);
    }

    public function getStrings(): array
    {
        return $this->stepTypesModel->getStrings();
    }

    public function getAllNodeTypes(): array
    {
        return $this->stepTypesModel->getAllStepTypes();
    }

    public function getAllNodeTypesByType(): array
    {
        return $this->stepTypesModel->getAllStepTypesByType();
    }
}
