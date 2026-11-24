<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class SendRay implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "advanced/ray.debug";
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
        return "sendRay";
    }

    public function getLabel(): string
    {
        return __("Send to Ray", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step transmits the workflow's data to Ray (by Spatie).", "post-expirator");
    }

    public function getIcon(): string
    {
        return "debug";
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
        return "debug";
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                "label" => __("Debug output", "post-expirator"),
                "description" => __("The data to be sent to Ray.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "data",
                        "type" => "debugData",
                        "label" => __("Data to output", "post-expirator"),
                        "defaultValue" => [
                            "expression" => "{{input}}",
                        ],
                    ],
                    [
                        "name" => "label",
                        "type" => "text",
                        "label" => __("Label", "post-expirator"),
                    ],
                    [
                        "name" => "color",
                        "type" => "rayColor",
                        "label" => __("Color", "post-expirator"),
                    ],
                ],
            ],
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
                ],
            ],
            "settings" => [
                "rules" => [
                    [
                        "rule" => "validExpression",
                        "field" => "data.expression",
                        "label" => __("Data to output", "post-expirator"),
                        "fieldLabel" => __("Debug output > Custom data > Data to output", "post-expirator"),
                        "allowedSlugs" => [
                            "input",
                        ],
                    ],
                ],
            ],
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
                "name" => "input",
                "type" => "input",
                "label" => __("Step input", "post-expirator"),
                "description" => __("The input data for this step.", "post-expirator"),
            ],
        ];
    }

    public function getCSSClass(): string
    {
        return "react-flow__node-debugAction";
    }

    public function getHandleSchema(): array
    {
        return [
            "target" => [
                [
                    "id" => "input",
                ],
            ],
            "source" => [
                [
                    "id" => "output",
                    "label" => __("Next", "post-expirator"),
                ],
            ],
        ];
    }

    public function isProFeature(): bool
    {
        return false;
    }
}
