<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class OnPostWorkflowEnable implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "trigger/core.manually-enabled-for-post";
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
        return "onPostWorkflowEnable";
    }

    public function getLabel(): string
    {
        return __("Manually run via checkbox", "post-expirator");
    }

    public function getDescription(): string
    {
        return __(
            "This trigger activates when the option is manually enabled. It can be enabled via the post editing screen or the Quick Edit panel.", // phpcs:ignore Generic.Files.LineLength.TooLong
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
                "description" => __("Settings for the trigger.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "checkboxLabel",
                        "type" => "text",
                        "label" => __("Checkbox label", "post-expirator"),
                        "description" => __(
                            "The label of the checkbox that enables the trigger.",
                            "post-expirator"
                        ),
                    ],
                ],
            ],
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
                'name' => 'post',
                'type' => 'post',
                'label' => __("Saved post", "post-expirator"),
                'description' => __("The post that was saved, with the new properties.", "post-expirator"),
            ],
            [
                "name" => "postId",
                "type" => "integer",
                "label" => __("Post ID", "post-expirator"),
                "description" => __("The ID of the post that was saved.", "post-expirator"),
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
                ],
            ],
        ];
    }

    public function isProFeature(): bool
    {
        return false;
    }
}
