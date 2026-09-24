<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class SetPostAuthors implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.set-post-authors";
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
        return "setPostAuthors";
    }

    public function getLabel(): string
    {
        return __("Set post authors", "post-expirator");
    }

    public function getDescription(): string
    {
        return __(
            "This step replaces a post's PublishPress Authors with the specified authors.",
            "post-expirator"
        );
    }

    public function getIcon(): string
    {
        return "groups";
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
        return "authors";
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                "label" => __("Target Post", "post-expirator"),
                "description" => __("Select which post will have its authors set.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "post",
                        "type" => "postInput",
                        "label" => __("Post", "post-expirator"),
                        "description" => __("The post whose authors will be set.", "post-expirator"),
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
                "label" => __("Authors", "post-expirator"),
                "description" => __(
                    "The authors to assign, as a comma-separated list of author slugs, emails, or IDs. "
                    . "This replaces the post's existing authors.",
                    "post-expirator"
                ),
                "fields" => [
                    [
                        "name" => "authors",
                        "type" => "expression",
                        "label" => __("Authors", "post-expirator"),
                        "description" => __("Comma-separated author slugs, emails, or term IDs.", "post-expirator"),
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
                    ["rule" => "hasIncomingConnection"],
                ],
            ],
            "settings" => [
                "rules" => [
                    ["rule" => "required", "field" => "post.variable"],
                    ["rule" => "required", "field" => "authors"],
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
            "target" => [["id" => "input"]],
            "source" => [["id" => "output", "label" => __("Next", "post-expirator")]],
        ];
    }

    public function isProFeature(): bool
    {
        return false;
    }
}
