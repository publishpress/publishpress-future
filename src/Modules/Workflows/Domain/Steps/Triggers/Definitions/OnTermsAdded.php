<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class OnTermsAdded implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "trigger/core.terms-added";
    }

    public function getElementaryType(): string
    {
        return StepTypesModel::STEP_TYPE_TRIGGER;
    }

    public function getReactFlowNodeType(): string
    {
        return "trigger";
    }

    public function getBaseSlug(): string
    {
        return "onTermsAdded";
    }

    public function getLabel(): string
    {
        return __("Terms are added to post", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This trigger activates when taxonomy terms are added to a post.", "post-expirator");
    }

    public function getIcon(): string
    {
        return "tag";
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
                "label" => __("Post Filter", "post-expirator"),
                "description" => __(
                    "Specify the criteria for posts that will trigger this action.",
                    "post-expirator"
                ),
                "fields" => [
                    [
                        "name" => "postQuery",
                        "type" => "postFilter",
                        "label" => __("Post filter", "post-expirator"),
                        "description" => __(
                            "The filter defines the posts that will trigger this action.",
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
            ],
            [
                "label" => __("Terms Filter", "post-expirator"),
                "description" => __(
                    "Specify which terms should trigger the workflow.",
                    "post-expirator"
                ),
                "fields" => [
                    [
                        "name" => "termsQuery",
                        "type" => "taxonomyTerms",
                        "label" => __("Terms filter", "post-expirator"),
                        "description" => __(
                            "Select the specific terms that will trigger this workflow.",
                            "post-expirator"
                        ),
                        "settings" => [
                            "optionToSelectAll" => false,
                        ],
                        "default" => [
                            "taxonomy" => "",
                            "terms" => [],
                        ],
                    ],
                ]
            ]
        ];
    }

    public function getValidationSchema(): array
    {
        return [
            "settings" => [
                "rules" => [
                    [
                        "rule" => "required",
                        "field" => "termsQuery.terms",
                        "label" => __("Terms", "post-expirator"),
                    ]
                ],
            ],
            "connections" => [
                "rules" => [
                    [
                        "rule" => "hasOutgoingConnection",
                    ],
                ]
            ],
        ];
    }

    public function getStepScopedVariablesSchema(): array
    {
        return [
            [
                "name" => "postBefore",
                "type" => "post",
                "label" => __("Post Before Update", "post-expirator"),
                "description" => __("The post before terms were added.", "post-expirator"),
            ],
            [
                "name" => "postAfter",
                "type" => "post",
                "label" => __("Post After Update", "post-expirator"),
                "description" => __("The post after terms were added.", "post-expirator"),
            ],
            [
                "name" => "postId",
                "type" => "integer",
                "label" => __("Post ID", "post-expirator"),
                "description" => __("The ID of the post that had terms added.", "post-expirator"),
            ],
        ];
    }

    public function getOutputSchema(): array
    {
        return $this->getStepScopedVariablesSchema();
    }

    public function getCSSClass(): string
    {
        return "react-flow__node-genericTrigger";
    }

    public function getHandleSchema(): array
    {
        return [
            "target" => [],
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
