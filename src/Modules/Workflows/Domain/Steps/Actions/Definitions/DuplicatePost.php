<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class DuplicatePost implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.duplicate-post";
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
        return "duplicatePost";
    }

    public function getLabel(): string
    {
        return __("Duplicate post", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step duplicates one or more posts.", "post-expirator");
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
                "description" => __("Select which post will be duplicated.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "post",
                        "type" => "postInput",
                        "label" => __("Post to Duplicate", "post-expirator"),
                        "description" => __(
                            "Choose the post that will be duplicated.",
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
                "label" => __("Duplication Options", "post-expirator"),
                "description" => __("Configure how the post should be duplicated.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "newPostStatus",
                        "type" => "postStatus",
                        "label" => __("New post status", "post-expirator"),
                        "description" => __("The status for the duplicated post.", "post-expirator"),
                    ],
                    [
                        "name" => "titlePrefix",
                        "type" => "text",
                        "label" => __("Title prefix", "post-expirator"),
                        "description" => __("Text to add before the original title. Leave empty for no prefix.", "post-expirator"),
                        "default" => __("Copy of ", "post-expirator"),
                    ],
                    [
                        "name" => "copyMeta",
                        "type" => "toggle",
                        "label" => __("Copy Post Meta", "post-expirator"),
                        "description" => __("If enabled, all post meta will be copied to the new post.", "post-expirator"),
                        "default" => false,
                    ],
                    [
                        "name" => "copyTaxonomies",
                        "type" => "toggle",
                        "label" => __("Copy Post Taxonomies", "post-expirator"),
                        "description" => __("If enabled, all post taxonomies will be copied to the new post.", "post-expirator"),
                        "default" => false,
                    ],
                    [
                        "name" => "copyFeaturedImage",
                        "type" => "toggle",
                        "label" => __("Copy Featured Image", "post-expirator"),
                        "description" => __("If enabled, the featured image will be copied to the new post.", "post-expirator"),
                        "default" => false,
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
                        "dataType" => ["post", "array:integer"],
                    ],
                ],
            ],
        ];
    }

    public function getStepScopedVariablesSchema(): array
    {
        return [
            [
                "name" => "newPostIds",
                "type" => "array",
                "itemsType" => "integer",
                "label" => __("Duplicated post IDs", "post-expirator"),
                "description" => __("The IDs of the newly created duplicate posts.", "post-expirator"),
            ],
        ];
    }

    public function getOutputSchema(): array
    {
        return [
            [
                "name" => "newPostIds",
                "type" => "array",
                "itemsType" => "integer",
                "label" => __("Duplicated post IDs", "post-expirator"),
                "description" => __("The IDs of the newly created duplicate posts.", "post-expirator"),
            ],
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
        return true;
    }
}
