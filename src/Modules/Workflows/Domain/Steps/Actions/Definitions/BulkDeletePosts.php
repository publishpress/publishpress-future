<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class BulkDeletePosts implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.bulk-post-delete";
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
        return "bulkDeletePosts";
    }

    public function getLabel(): string
    {
        return __("Delete multiple posts", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step trashes or deletes every post matching the query.", "post-expirator");
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
                "label" => __("Post Query", "post-expirator"),
                "description" => __(
                    "The query defines which posts will be trashed or deleted.",
                    "post-expirator"
                ),
                "fields" => [
                    [
                        "name" => "postQuery",
                        "type" => "postQuery",
                        "label" => __("Post query", "post-expirator"),
                        "description" => __(
                            "Every post matching this query will be trashed or deleted.",
                            "post-expirator"
                        ),
                        "default" => [
                            "postSource" => "custom",
                            "postType" => ["post"],
                            "postId" => [],
                            "postStatus" => [],
                        ],
                    ],
                ],
            ],
            [
                "label" => __("Deletion", "post-expirator"),
                "description" => __("Choose whether to trash or permanently delete the posts.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "mode",
                        "type" => "select",
                        "label" => __("Deletion mode", "post-expirator"),
                        "description" => __(
                            "\"Move to trash\" can be undone. \"Delete permanently\" cannot.",
                            "post-expirator"
                        ),
                        "settings" => [
                            "options" => [
                                [
                                    "value" => "trash",
                                    "label" => __("Move to trash", "post-expirator"),
                                ],
                                [
                                    "value" => "delete",
                                    "label" => __("Delete permanently", "post-expirator"),
                                ],
                            ],
                        ],
                        "default" => "trash",
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
        return false;
    }
}
