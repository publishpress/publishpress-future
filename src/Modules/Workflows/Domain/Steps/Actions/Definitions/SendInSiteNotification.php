<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class SendInSiteNotification implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.send-in-site-notification";
    }

    public static function getDefaultSubject()
    {
        return __('PublishPress Workflow: {{global.workflow.title}}', 'post-expirator');
    }

    public static function getDefaultMessage()
    {
        return __(
            'This is a message sent by PublishPress Workflow: {{global.workflow.title}}.',
            'post-expirator'
        );
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
        return "sendInSiteNotification";
    }

    public function getLabel(): string
    {
        return __("Send in-site notification", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step dispatches a message to the in-site notification.", "post-expirator");
    }

    public function getIcon(): string
    {
        return "bell";
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
        return "messages";
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                "label" => __("Recipient", "post-expirator"),
                "description" => __("The recipient of the in-site notification.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "recipient",
                        "type" => "expression",
                        "label" => __("Recipients", "post-expirator"),
                        "description" => __("A comma-separated list of user names, ids, emails or user roles to send the message to.", "post-expirator"),
                        "default" => [
                            "expression" => "administrator",
                        ],
                    ],
                ],
            ],
            [
                "label" => __("Message", "post-expirator"),
                "fields" => [
                    [
                        "name" => "subject",
                        "type" => "expression",
                        "label" => __("Subject", "post-expirator"),
                        "default" => [
                            "expression" => self::getDefaultSubject(),
                        ],
                    ],
                    [
                        "name" => "message",
                        "type" => "expression",
                        "label" => __("Message", "post-expirator"),
                        "default" => [
                            "expression" => self::getDefaultMessage(),
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
                        "field" => "recipient.expression",
                        "label" => __("Recipient", "post-expirator"),
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
                        "field" => "recipient.expression",
                        "label" => __("Recipient", "post-expirator"),
                        "fieldLabel" => __("Recipient > Recipients", "post-expirator"),
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
                "name" => "readingTime",
                "type" => "datetime",
                "label" => __("Reading time", "post-expirator"),
                "description" => __("The reading time of the admin notification.", "post-expirator"),
            ],
            [
                "name" => "readBy",
                "type" => "user",
                "label" => __("Read by", "post-expirator"),
                "description" => __("The user who read the admin notification.", "post-expirator"),
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
                ],
            ],
            "source" => [
                [
                    "id" => "output",
                    "label" => __("Next", "post-expirator"),
                ],
                [
                    "id" => "read",
                    "label" => __("On dismiss", "post-expirator"),
                ],
            ],
        ];
    }

    public function isProFeature(): bool
    {
        return true;
    }
}
