<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class Conditional implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "advanced/core.conditional-split";
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
        return "conditional";
    }

    public function getLabel(): string
    {
        return __("Conditional", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step allows you to continue the workflow only if certain conditions are met. It is not required to have both a True and a False option.", "post-expirator");
    }

    public function getIcon(): string
    {
        return "route";
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
        return "flow";
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                "label" => __("Condition", "post-expirator"),
                "description" => __(
                    "", // phpcs:ignore Generic.Files.LineLength.TooLong
                    "post-expirator"
                ),
                "fields" => [
                    [
                        "name" => "conditions",
                        "type" => "conditional",
                        "label" => __("Conditions", "post-expirator"),
                        "description" => __("The conditions that must be met for deciding which branch to take.", "post-expirator"),
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
                    [
                        "rule" => "hasOutgoingConnection",
                    ],
                ],
            ],
            "settings" => [
                "rules" => [
                    [
                        "rule" => "validExpression",
                        "field" => "conditions.natural",
                        "label" => __("Conditions", "post-expirator"),
                        "fieldLabel" => __("Conditions", "post-expirator"),
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
            [
                "name" => "branch",
                "type" => "string",
                "label" => __("Branch", "post-expirator"),
                "description" => __("Shows which path was taken: 'true' if conditions were met, or 'false' if they weren't.", "post-expirator"),
                "priority" => 20,
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
                ],
            ],
            "source" => [
                [
                    "id" => "true",
                    "label" => __("True", "post-expirator"),
                ],
                [
                    "id" => "false",
                    "label" => __("False", "post-expirator"),
                ],
            ],
        ];
    }

    public function isProFeature(): bool
    {
        return true;
    }
}
