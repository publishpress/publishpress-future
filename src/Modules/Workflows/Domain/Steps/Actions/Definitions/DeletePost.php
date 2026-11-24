<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class DeletePost implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.delete-post";
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
        return "deletePost";
    }

    public function getLabel(): string
    {
        return __("Delete post", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step deletes a post.", "post-expirator");
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
                "label" => __("Target Post", "post-expirator"),
                "description" => __("Select which post will be deleted.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "post",
                        "type" => "postInput",
                        "label" => __("Post to Delete", "post-expirator"),
                        "description" => __(
                            "Choose the post that will be deleted.",
                            "post-expirator"
                        ),
                        "default" => [
                            "variable" => [
                                "rule" => "first",
                                "dataType" => "post",
                            ],
                        ],
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
