<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class DeletePostMeta implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.post-meta-delete";
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
        return "deletePostMeta";
    }

    public function getLabel(): string
    {
        return __("Delete post meta", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step deletes post meta from a post.", "post-expirator");
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
                "description" => __("Select which post will have post meta deleted.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "post",
                        "type" => "postInput",
                        "label" => __("Post to Delete Meta", "post-expirator"),
                        "description" => __(
                            "Choose the post that will have its meta deleted.",
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
            [
                "label" => __("Meta", "post-expirator"),
                "description" => __("The meta to delete from the post.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "metaKey",
                        "type" => "text",
                        "label" => __("Meta key", "post-expirator"),
                        "description" => __("The meta key to delete from the post.", "post-expirator"),
                    ],
                    [
                        "name" => "metaValue",
                        "type" => "expression",
                        "label" => __("Meta value", "post-expirator"),
                        "description" => __("Specify the meta value to delete from the post. If empty, all meta with the same key will be deleted.", "post-expirator"),
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
                        "rule" => "required",
                        "field" => "metaKey",
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
            [
                "name" => "metaId",
                "type" => "integer",
                "label" => __("Meta ID", "post-expirator"),
                "description" => __("The ID of the meta added to the post.", "post-expirator"),
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
        return true;
    }
}
