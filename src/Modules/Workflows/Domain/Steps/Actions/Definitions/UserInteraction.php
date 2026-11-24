<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class UserInteraction implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "advanced/user-interaction";
    }

    public function getElementaryType(): string
    {
        return StepTypesModel::STEP_TYPE_ADVANCED;
    }

    public function getReactFlowNodeType(): string
    {
        return "generic";
    }

    public function getBaseSlug(): string
    {
        return "userInteraction";
    }

    public function getLabel(): string
    {
        return __("User interaction", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step requires user action before the workflow can proceed. The actions are done via the in-site notification area.", "post-expirator");
    }

    public function getIcon(): string
    {
        return "interactive";
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
        return "site";
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                "label" => __("Responders", "post-expirator"),
                "description" => __("Choose which users will see notifications from this action.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "responders",
                        "type" => "expression",
                        "description" => __(
                            "A comma-separated list of user names, ids, emails or user roles that can interact with this step.",
                            "post-expirator"
                        ),
                        "default" => [
                            "expression" => "administrator",
                        ],
                    ],
                ],
            ],
            [
                "label" => __("Message", "post-expirator"),
                "description" => __("Specify the message to be displayed to the responders.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "subject",
                        "type" => "expression",
                        "label" => __("Subject", "post-expirator"),
                        "default" => [
                            "expression" => __('PublishPress Workflow: {{global.workflow.title}}', 'post-expirator'),
                        ],
                    ],
                    [
                        "name" => "message",
                        "type" => "expression",
                        "label" => __("Message", "post-expirator"),
                        "default" => [
                            "expression" => __("Please select an option to continue the workflow: {{global.workflow.title}}", "post-expirator"),
                        ],
                    ],
                    [
                        "name" => "notificationType",
                        "type" => "select",
                        "label" => __("Notification type", "post-expirator"),
                        "settings" => [
                            "options" => [
                                [
                                    "value" => "info",
                                    "label" => __("Info", "post-expirator"),
                                ],
                                [
                                    "value" => "success",
                                    "label" => __("Success", "post-expirator"),
                                ],
                                [
                                    "value" => "warning",
                                    "label" => __("Warning", "post-expirator"),
                                ],
                                [
                                    "value" => "error",
                                    "label" => __("Error", "post-expirator"),
                                ],
                            ],
                        ],
                        "default" => "info",
                    ],
                ],
            ],
            [
                "label" => __("Options", "post-expirator"),
                "description" => __("Specify the options the user can choose from.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "options",
                        "type" => "interactiveCustomOptions",
                        "default" => [
                            [
                                "name" => "approve",
                                "label" => __("Approve", "post-expirator"),
                                "hint" => __("Approve the workflow", "post-expirator"),
                            ],
                            [
                                "name" => "dismiss",
                                "label" => __("Deny", "post-expirator"),
                                "hint" => __("Deny the workflow", "post-expirator"),
                            ],
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
                        "rule" => "hasIncomingConnection",
                    ],
                ],
            ],
            "settings" => [
                "rules" => [
                    [
                        "rule" => "required",
                        "field" => "responders.expression",
                        "label" => __("Responders", "post-expirator"),
                    ],
                    [
                        "rule" => "required",
                        "field" => "subject.expression",
                        "label" => __("Subject", "post-expirator"),
                    ],
                    [
                        "rule" => "required",
                        "field" => "message.expression",
                        "label" => __("Message", "post-expirator"),
                    ],
                    [
                        "rule" => "validExpression",
                        "field" => "responders.expression",
                        "label" => __("Responders", "post-expirator"),
                        "fieldLabel" => __("Responders > Responders", "post-expirator"),
                    ],
                    [
                        "rule" => "validExpression",
                        "field" => "subject.expression",
                        "label" => __("Subject", "post-expirator"),
                        "fieldLabel" => __("Message > Subject", "post-expirator"),
                    ],
                    [
                        "rule" => "validExpression",
                        "field" => "message.expression",
                        "label" => __("Message", "post-expirator"),
                        "fieldLabel" => __("Message > Message", "post-expirator"),
                    ],
                    [
                        "rule" => "validOptions",
                        "field" => "options",
                        "label" => __("Options", "post-expirator"),
                        "fieldLabel" => __("Options > Options", "post-expirator"),
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
            [
                "name" => "responders",
                "type" => "string",
                "label" => __("Responders", "post-expirator"),
                "description" => __("The responders to the interactive delay, as a list of user ids.", "post-expirator"),
            ],
            [
                "name" => "optionName",
                "type" => "string",
                "label" => __("Option name", "post-expirator"),
                "description" => __("The name of the option selected by the user.", "post-expirator"),
            ],
        ];
    }

    public function getCSSClass(): string
    {
        return "react-flow__node-genericAdvanced";
    }

    public function getHandleSchema(): array
    {
        return [
            "target" => [
                [
                    "id" => "input",
                ],
            ],
            "source" => [
                [
                    "id" => "output",
                    "type" => "__dynamic__:options",
                    "label" => __("After interaction", "post-expirator"),
                ],
            ],
        ];
    }

    public function isProFeature(): bool
    {
        return true;
    }
}
