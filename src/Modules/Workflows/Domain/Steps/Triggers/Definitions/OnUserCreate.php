<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class OnUserCreate implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "trigger/core.user-create";
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
        return "onUserCreate";
    }

    public function getLabel(): string
    {
        return __("New user is created", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This trigger activates when a new user is created.", "post-expirator");
    }

    public function getIcon(): string
    {
        return "admin-users";
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
        return "user";
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                "label" => __("User Filter", "post-expirator"),
                "description" => __(
                    "Specify the criteria for users whose creation will trigger this action.",
                    "post-expirator"
                ),
                "fields" => [
                    [
                        "name" => "userQuery",
                        "type" => "userQuery",
                        "label" => __("User query", "post-expirator"),
                        "description" => __(
                            "The query defines the users whose creation will trigger this action.",
                            "post-expirator"
                        ),
                        "settings" => [
                            "labels" => [
                                "userRole" => __("User roles", "post-expirator"),
                            ],
                            "userRoleDescription" => __(
                                "The trigger activates only when the new user has one of the "
                                . "selected roles. Leave empty to trigger for any role.",
                                "post-expirator"
                            ),
                        ],
                        "default" => [
                            "userSource" => "custom",
                            "userRole" => [],
                            "userId" => [],
                        ],
                    ],
                ]
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
                ]
            ]
        ];
    }

    public function getStepScopedVariablesSchema(): array
    {
        return [
            [
                "name" => "user",
                "type" => "user",
                "label" => __("Created user", "post-expirator"),
                "description" => __("The user that was created.", "post-expirator"),
            ],
            [
                "name" => "userId",
                "type" => "integer",
                "label" => __("Created user's ID", "post-expirator"),
                "description" => __("The ID of the user that was created.", "post-expirator"),
            ],
            [
                "name" => "roles",
                "type" => "array",
                "label" => __("User roles", "post-expirator"),
                "description" => __("The roles assigned to the user on creation.", "post-expirator"),
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
