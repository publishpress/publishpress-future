<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class OnPostMetaChange implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "trigger/core.post-meta-changeds";
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
        return "onPostMetaChange";
    }

    public function getLabel(): string
    {
        return __("Post meta changed", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This trigger activates when a post meta field is changed.", "post-expirator");
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
                ],
            ],
            [
                "label" => __("Meta settings", "post-expirator"),
                "description" => __("Specify the criteria for the meta to watch for changes.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "metaKeys",
                        "type" => "text",
                        "label" => __("Meta keys", "post-expirator"),
                        "description" => __("This field allow you to choose the post meta that will trigger this action.", "post-expirator"),
                        "default" => [],
                        'helpUrl' => 'https://publishpress.com/knowledge-base/future-postmeta-field',
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
                        "rule" => "hasOutgoingConnection",
                    ],
                ],
            ],
        ];
    }

    public function getStepScopedVariablesSchema(): array
    {
        return [
            [
                "name" => "post",
                "type" => "post",
                "label" => __("Saved post", "post-expirator"),
                "description" => __("The post that was saved, with the new properties.", "post-expirator"),
            ],
            [
                "name" => "postId",
                "type" => "integer",
                "label" => __("Post ID", "post-expirator"),
                "description" => __("The ID of the post that was updated.", "post-expirator"),
            ],
        ];
    }

    public function getOutputSchema(): array
    {
        return [
            [
                'name' => 'post',
                'type' => 'post',
                'label' => __("Post", "post-expirator"),
                'description' => __("The post that triggered this action.", "post-expirator"),
            ],
            [
                "name" => "postId",
                "type" => "integer",
                "label" => __("Post ID", "post-expirator"),
                "description" => __("The ID of the post that was updated.", "post-expirator"),
            ],
            [
                'name' => 'action',
                'type' => 'string',
                'label' => __("Action", "post-expirator"),
                'description' => __("The action taken when the meta key is changed: 'added', 'updated' or 'deleted'.", "post-expirator"),
            ],
            [
                'name' => 'metaId',
                'type' => 'integer',
                'label' => __("Meta ID", "post-expirator"),
                'description' => __("The meta ID.", "post-expirator"),
            ],
            [
                'name' => 'metaKey',
                'type' => 'string',
                'label' => __("Meta key", "post-expirator"),
                'description' => __("The meta key that was changed.", "post-expirator"),
            ],
            [
                'name' => 'metaValue',
                'type' => 'string',
                'label' => __("New meta value", "post-expirator"),
                'description' => __("The new meta value.", "post-expirator"),
            ],
            [
                'name' => 'oldMetaValue',
                'type' => 'string',
                'label' => __("Old meta value", "post-expirator"),
                'description' => __("The meta value before the change.", "post-expirator"),
            ],
        ];
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
                ],
            ],
        ];
    }

    public function isProFeature(): bool
    {
        return true;
    }
}
