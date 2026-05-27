<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class DoAction implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "advanced/do.action";
    }

    public function getElementaryType(): string
    {
        return StepTypesModel::STEP_TYPE_ADVANCED;
    }

    public function getReactFlowNodeType(): string
    {
        return "generic";
    }

    public function getBaseSlug(): string
    {
        return "doAction";
    }

    public function getLabel(): string
    {
        return __("Do custom action", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step executes a custom action.", "post-expirator");
    }

    public function getIcon(): string
    {
        return "action";
    }

    public function getFrecency(): int
    {
        return 1;
    }

    public function getVersion(): int
    {
        return 1;
    }

    public function getCategory(): string
    {
        return "site";
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                "label" => __("Action", "post-expirator"),
                "description" => __(
                    "Enter the action hook to trigger. Only hooks registered through the 'publishpress_future_allowed_action_hooks' filter are allowed.",
                    "post-expirator"
                ),
                "fields" => [
                    [
                        "name" => "hook",
                        "type" => "text",
                        "label" => __("Hook", "post-expirator"),
                        "description" => __(
                            "The hook that will be executed.",
                            "post-expirator"
                        ),
                        "default" => "",
                    ],
                    [
                        "name" => "args",
                        "type" => "actionArgs",
                        "label" => __("Action arguments", "post-expirator"),
                        "description" => __(
                            "The arguments to pass to the action. These arguments will be available as variables in subsequent workflow steps.",
                            "post-expirator"
                        ),
                        "default" => [],
                        "settings" => [
                            "withExpression" => true,
                        ],
                    ],
                ]
            ]
        ];
    }

    public function getValidationSchema(): array
    {
        return [
            "connections" => [
                "rules" => [
                    [
                        "rule" => "hasIncomingConnection",
                    ],
                ]
            ],
            "settings" => [
                "rules" => [
                    [
                        "rule" => "required",
                        "field" => "hook",
                        "message" => __("The action hook is required.", "post-expirator"),
                    ],
                    [
                        "rule" => "dataType",
                        "type" => "nameValuePairList",
                        "field" => "args",
                        "label" => __("Action arguments", "post-expirator"),
                    ],
                ]
            ]
        ];
    }

    public function getStepScopedVariablesSchema(): array
    {
        return [];
    }

    public function getOutputSchema(): array
    {
        return [
            [
                "name" => "args",
                "type" => "__dynamic__:args",
                "label" => __("Action arguments", "post-expirator"),
                "description" => __("The arguments to pass to the action.", "post-expirator"),
            ],
        ];
    }

    public function getCSSClass(): string
    {
        return "react-flow__node-genericAdvanced";
    }

    public function getHandleSchema(): array
    {
        return [
            "target" => [
                [
                    "id" => "input",
                ]
            ],
            "source" => [
                [
                    "id" => "output",
                    "label" => __("Next", "post-expirator"),
                ]
            ]
        ];
    }

    public function isProFeature(): bool
    {
        return true;
    }
}
