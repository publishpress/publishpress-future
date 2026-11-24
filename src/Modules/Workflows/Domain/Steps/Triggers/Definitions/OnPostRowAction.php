<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class OnPostRowAction implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "trigger/core.post-row-action";
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
        return "onPostRowAction";
    }

    public function getLabel(): string
    {
        return __("Manually run via posts row action", "post-expirator");
    }

    public function getDescription(): string
    {
        return __(
            "This trigger creates a custom post row action and activates when the row action is clicked in the Posts list screen.", // phpcs:ignore Generic.Files.LineLength.TooLong
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
        return 2;
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
                "label" => __("Row Action", "post-expirator"),
                "description" => __(
                    "Configure the post row action that will be created in the Posts list screen.",
                    "post-expirator"
                ),
                "fields" => [
                    [
                        "name" => "actionLabel",
                        "type" => "text",
                        "label" => __("Action label", "post-expirator"),
                        "description" => __(
                            "The label of the action created in the post row for this trigger.",
                            "post-expirator"
                        ),
                        "settings" => [
                            "placeholder" => __("Run workflow", "post-expirator"),
                        ],
                    ],
                    [
                        "name" => "askForConfirmation",
                        "type" => "askForConfirmation",
                        "label" => __("Ask for confirmation", "post-expirator"),
                        "description" => __(
                            "If enabled, the user will be asked to confirm the action before it is executed.",
                            "post-expirator"
                        ),
                        "default" => [
                            "enabled" => false,
                            "message" => __("Are you sure you want to run this action?", "post-expirator"),
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
                "name" => "post",
                "type" => "post",
                "label" => __("Triggered post", "post-expirator"),
                "description" => __("The post that triggered the action.", "post-expirator"),
            ],
            [
                "name" => "postId",
                "type" => "integer",
                "label" => __("Post ID", "post-expirator"),
                "description" => __("The ID of the post that triggered the action.", "post-expirator"),
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
        return true;
    }
}
