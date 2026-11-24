<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class AppendDebugLog implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "advanced/log.add";
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
        return "appendDebugLog";
    }

    public function getLabel(): string
    {
        return __("Send to debug log", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step transmits the workflow's data to the debug log.", "post-expirator");
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
                "description" => __("The message to be sent to the debug log.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "message",
                        "type" => "expression",
                        "label" => __("Message", "post-expirator"),
                    ],
                    [
                        "name" => "level",
                        "type" => "debugLevels",
                        "label" => __("Level", "post-expirator"),
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
                        "field" => "message.expression",
                        "label" => __("Debug output", "post-expirator"),
                        "fieldLabel" => __("Debug output > Message", "post-expirator"),
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
