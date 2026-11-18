<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class OnPostAuthorsChanged implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "trigger/core.post-authors-changed";
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
        return "onPostAuthorsChanged";
    }

    public function getLabel(): string
    {
        return __("Post authors are changed", "post-expirator");
    }

    public function getDescription(): string
    {
        return __(
            "This trigger activates when a post's PublishPress Authors are changed.",
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
        return [];
    }

    public function getValidationSchema(): array
    {
        return [
            "connections" => [
                "rules" => [
                    ["rule" => "hasOutgoingConnection"],
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
                "label" => __("Post", "post-expirator"),
                "description" => __("The post whose authors changed.", "post-expirator"),
            ],
            [
                "name" => "postId",
                "type" => "integer",
                "label" => __("Post ID", "post-expirator"),
                "description" => __("The ID of the post whose authors changed.", "post-expirator"),
            ],
            [
                "name" => "authors",
                "type" => "array",
                "label" => __("Author names", "post-expirator"),
                "description" => __("The display names of the post's authors.", "post-expirator"),
            ],
            [
                "name" => "authorEmails",
                "type" => "array",
                "label" => __("Author emails", "post-expirator"),
                "description" => __("The email addresses of the post's authors.", "post-expirator"),
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
