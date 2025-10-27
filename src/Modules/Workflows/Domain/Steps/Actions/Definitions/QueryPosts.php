<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class QueryPosts implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "advanced/core.query-post";
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
        return "queryPosts";
    }

    public function getLabel(): string
    {
        return __("Query posts", "post-expirator");
    }

    public function getDescription(): string
    {
        return __(
            "This step defines the posts that will be passed to the next step in the workflow.",
            "post-expirator"
        );
    }

    public function getIcon(): string
    {
        return "db-query";
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
        return "db-query";
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                "label" => __("Post Query", "post-expirator"),
                "fields" => [
                    [
                        "name" => "postQuery",
                        "type" => "postQuery",
                        "label" => __("Post query", "post-expirator"),
                        "description" => __(
                            "The query defines the posts that will be retrieved by this action.",
                            "post-expirator"
                        ),
                        "default" => [
                            "postSource" => "custom",
                            "postType" => ["post"],
                            "postId" => [],
                            "postStatus" => [],
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
                    [
                        "rule" => "hasOutgoingConnection",
                    ]
                ],
            ],
        ];
    }

    public function getStepScopedVariablesSchema(): array
    {
        return [
            [
                "name" => "posts",
                "type" => "array",
                "itemsType" => "integer",
                "label" => __("Posts from the Query posts step", "post-expirator"),
                "description" => __("The posts found following the criteria of the query.", "post-expirator"),
            ],
        ];
    }

    public function getOutputSchema(): array
    {
        return [
            [
                "name" => "posts",
                "type" => "array",
                "itemsType" => "integer",
                "label" => __("Posts from the Query posts step", "post-expirator"),
                "description" => __("The posts found following the criteria of the query.", "post-expirator"),
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
        return "react-flow__node-queryAction";
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
