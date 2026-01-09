<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class OnUserRoleChange implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "trigger/core.user-role-changed";
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
        return "onUserRoleChange";
    }

    public function getLabel(): string
    {
        return __("User role is changed", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This trigger activates when a user's role is changed.", "post-expirator");
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
                "label" => __("User Query", "post-expirator"),
                "description" => __(
                    "Specify the criteria for users whose role changes will trigger this action.",
                    "post-expirator"
                ),
                "fields" => [
                    [
                        "name" => "userQuery",
                        "type" => "userQuery",
                        "label" => __("User query", "post-expirator"),
                        "description" => __(
                            "The query defines the users that will trigger this action.",
                            "post-expirator"
                        ),
                        "settings" => [
                            "labels" => [
                                "userRole" => __(
                                    "User Role before change",
                                    "post-expirator"
                                ),
                                "userRoleAfter" => __(
                                    "User Role after change",
                                    "post-expirator"
                                ),
                            ],
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

        ];
    }

    public function getOutputSchema(): array
    {
        return [
            [
                'name' => 'user',
                'type' => 'user',
                'label' => __("User that data was changed for", "post-expirator"),
                'description' => __("The user that data was changed for.", "post-expirator"),
            ],
            [
                'name' => 'addedRoles',
                'type' => 'array',
                'label' => __("Added roles", "post-expirator"),
                'description' => __("The roles that were added to the user.", "post-expirator"),
            ],
            [
                'name' => 'removedRoles',
                'type' => 'array',
                'label' => __("Removed roles", "post-expirator"),
                'description' => __("The roles that were removed from the user.", "post-expirator"),
            ]
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
                ]
            ]
        ];
    }

    public function isProFeature(): bool
    {
        return true;
    }
}
