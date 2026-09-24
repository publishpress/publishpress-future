<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;
use PublishPress\Future\Modules\Workflows\Models\UserRolesModel;

class RemoveUserRole implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.user-remove-role";
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
        return "removeUserRole";
    }

    public function getLabel(): string
    {
        return __("Remove user role", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step removes a role from the user.", "post-expirator");
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
        $userRolesModel = new UserRolesModel();

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
                "label" => __("Role", "post-expirator"),
                "description" => __("The role to remove from the user.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "role",
                        "type" => "select",
                        "label" => __("Role to remove", "post-expirator"),
                        "description" => __("The role to remove from the user.", "post-expirator"),
                        "settings" => [
                            "options" => $userRolesModel->getUserRolesAsOptions(),
                        ],
                        "default" => "",
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
            "settings" => [
                "rules" => [
                    [
                        "rule" => "required",
                        "field" => "role",
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
