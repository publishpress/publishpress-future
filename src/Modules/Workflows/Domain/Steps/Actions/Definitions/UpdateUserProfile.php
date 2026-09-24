<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class UpdateUserProfile implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.user-update";
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
        return "updateUserProfile";
    }

    public function getLabel(): string
    {
        return __("Update user profile", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step updates profile fields for the user.", "post-expirator");
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
                "label" => __("Target User", "post-expirator"),
                "description" => __("Select which user this step will act on.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "userQuery",
                        "type" => "userQuery",
                        "label" => __("User to act on", "post-expirator"),
                        "description" => __(
                            "Choose the user this step will act on. By default it acts on the "
                            . "user received from the trigger.",
                            "post-expirator"
                        ),
                        "settings" => [
                            "acceptsInput" => true,
                            "labels" => [
                                "userRole" => __("User roles", "post-expirator"),
                            ],
                        ],
                        "default" => [
                            "userSource" => "input",
                            "userRole" => [],
                            "userId" => [],
                        ],
                    ],
                ],
            ],
            [
                "label" => __("Profile fields", "post-expirator"),
                "description" => __(
                    "Only fields with a value will be updated. Leave a field empty to keep it unchanged.",
                    "post-expirator"
                ),
                "fields" => [
                    [
                        "name" => "displayName",
                        "type" => "expression",
                        "label" => __("Display name", "post-expirator"),
                        "description" => __("The public display name.", "post-expirator"),
                    ],
                    [
                        "name" => "firstName",
                        "type" => "expression",
                        "label" => __("First name", "post-expirator"),
                    ],
                    [
                        "name" => "lastName",
                        "type" => "expression",
                        "label" => __("Last name", "post-expirator"),
                    ],
                    [
                        "name" => "email",
                        "type" => "expression",
                        "label" => __("Email", "post-expirator"),
                        "description" => __("The user's email address.", "post-expirator"),
                    ],
                    [
                        "name" => "url",
                        "type" => "expression",
                        "label" => __("Website", "post-expirator"),
                    ],
                    [
                        "name" => "description",
                        "type" => "expression",
                        "label" => __("Biographical info", "post-expirator"),
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
        return false;
    }
}
