<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class UpdatePost implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.post-update";
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
        return "updatePost";
    }

    public function getLabel(): string
    {
        return __("Update post details", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step updates key information about each post such as the data, title, content, author, and more.", "post-expirator");
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
                "description" => __("Select which post will be updated.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "post",
                        "type" => "postInput",
                        "label" => __("Post to Update", "post-expirator"),
                        "description" => __(
                            "Choose the post that will be updated.",
                            "post-expirator"
                        ),
                        "default" => [
                            "variable" => [
                                "rule" => "first",
                                "dataType" => "post",
                            ]
                        ],
                    ],
                ],
            ],
            [
                "label" => __("Post Data", "post-expirator"),
                "description" => __("Select which data should be updated in the post.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "postData",
                        "type" => "postData",
                        "label" => __("Post data", "post-expirator"),
                        "description" => __("The data to update for the post.", "post-expirator"),
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
                    [
                        "rule" => "validExpression",
                        "field" => "postData.title.expression",
                        "label" => __("Post title", "post-expirator"),
                        "fieldLabel" => __("Post data > Post title", "post-expirator"),
                    ],
                    [
                        "rule" => "validExpression",
                        "field" => "postData.content.expression",
                        "label" => __("Post content", "post-expirator"),
                        "fieldLabel" => __("Post data > Post content", "post-expirator"),
                    ],
                    [
                        "rule" => "validExpression",
                        "field" => "postData.excerpt.expression",
                        "label" => __("Post excerpt", "post-expirator"),
                        "fieldLabel" => __("Post data > Post excerpt", "post-expirator"),
                    ],
                    [
                        "rule" => "validExpression",
                        "field" => "postData.date.expression",
                        "label" => __("Post date", "post-expirator"),
                        "fieldLabel" => __("Post data > Post date", "post-expirator"),
                    ],
                    [
                        "rule" => "validExpression",
                        "field" => "postData.name.expression",
                        "label" => __("Post name", "post-expirator"),
                        "fieldLabel" => __("Post data > Post name", "post-expirator"),
                    ],
                    [
                        "rule" => "validExpression",
                        "field" => "postData.password.expression",
                        "label" => __("Post password", "post-expirator"),
                        "fieldLabel" => __("Post data > Post password", "post-expirator"),
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
                "name" => "updatedPost",
                "type" => "post",
                "label" => __("Updated post", "post-expirator"),
                "description" => __("The post data after the update.", "post-expirator"),
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
        return true;
    }
}
