<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class DeactivatePostWorkflow implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.post-deactivate-workflow";
    }

    public function getElementaryType(): string
    {
        return StepTypesModel::STEP_TYPE_ACTION;
    }

    public function getReactFlowNodeType(): string
    {
        return "generic";
    }

    public function getBaseSlug(): string
    {
        return "deactWorkflowForPost";
    }

    public function getLabel(): string
    {
        return __("Deactivate workflow for post", "post-expirator");
    }

    public function getDescription(): string
    {
        return __(
            "This step enables you to deactivate the \"Manually enabled via checkbox\" workflow after use.",
            "post-expirator"
        );
    }

    public function getIcon(): string
    {
        return "media-document";
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
        return "post";
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                "label" => __("Settings", "post-expirator"),
                "description" => __("Select which post will have its workflow deactivated.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "post",
                        "type" => "postInput",
                        "label" => __("Post to Deactivate Workflow", "post-expirator"),
                        "description" => __(
                            "Choose the post that will have its workflow deactivated.",
                            "post-expirator"
                        ),
                        "default" => [
                            "variable" => [
                                "rule" => "first",
                                "dataType" => "post",
                            ],
                        ],
                    ],
                    [
                        "name" => "workflow",
                        "type" => "manualWorkflowInput",
                        "default" => [
                            "variable" => [
                                "rule" => "first",
                                "dataType" => "workflow",
                            ],
                        ],
                        "label" => __("Workflow to Deactivate", "post-expirator"),
                        "description" => __(
                            "Choose the manually enabling workflow that will be deactivated for the selected post.", // phpcs:ignore Generic.Files.LineLength.TooLong
                            "post-expirator"
                        ),
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
                        "rule" => "required",
                        "field" => "post.variable",
                        "label" => __("Post", "post-expirator"),
                    ],
                    [
                        "rule" => "required",
                        "field" => "workflow.variable",
                        "label" => __("Workflow", "post-expirator"),
                    ],
                    [
                        "rule" => "validVariable",
                        "field" => "post.variable",
                        "fieldLabel" => __("Post", "post-expirator"),
                        "dataType" => "post",
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
        return "react-flow__node-genericAction";
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
