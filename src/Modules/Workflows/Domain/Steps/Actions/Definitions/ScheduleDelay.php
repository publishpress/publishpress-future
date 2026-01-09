<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class ScheduleDelay implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "advanced/core.schedule";
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
        return "scheduleDelay";
    }

    public function getLabel(): string
    {
        return __("Schedule delay", "post-expirator");
    }

    public function getDescription(): string
    {
        return __(
            "This step allows you to run the next part of a workflow at a specific time, or relative to another date. You can also use this step to repeat the next part of the workflow.", // phpcs:ignore Generic.Files.LineLength.TooLong
            "post-expirator"
        );
    }

    public function getIcon(): string
    {
        return "schedule";
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
        return "async";
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                "label" => __("Schedule", "post-expirator"),
                "description" => __("A scheduled delay between steps.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "schedule",
                        "type" => "schedule",
                        "label" => __("When to run", "post-expirator"),
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
                    [
                        "rule" => "hasOutgoingConnection",
                    ],
                ],
            ],
            "settings" => [
                "rules" => [
                    [
                        "rule" => "validExpression",
                        "field" => "schedule.uniqueIdExpression.expression",
                        "label" => __("Unique ID Expression", "post-expirator"),
                        "fieldLabel" => __("Schedule > Unique ID Expression", "post-expirator"),
                    ],
                    [
                        "rule" => "required",
                        "field" => "schedule.customDateSource.expression",
                        "label" => __("Custom date source", "post-expirator"),
                        "condition" => [
                            "field" => "schedule.dateSource",
                            "value" => "custom",
                        ],
                    ],
                    [
                        "rule" => "hasVariableSyntax",
                        "field" => "schedule.customDateSource.expression",
                        "label" => __("Custom date source", "post-expirator"),
                        "fieldLabel" => __("Schedule > Custom date source", "post-expirator"),
                    ],
                ],
            ],
        ];
    }

    public function getStepScopedVariablesSchema(): array
    {
        return [
            [
                "name" => "schedule_date",
                "type" => "datetime",
                "label" => __("Schedule date", "post-expirator"),
                "description" => __("The date and time when the step will run.", "post-expirator"),
            ],
            [
                "name" => "action_uid_hash",
                "type" => "string",
                "label" => __("Action UID hash", "post-expirator"),
                "description" => __("The unique ID hash of the action that will run.", "post-expirator"),
            ],
            [
                "name" => "repeat_count",
                "type" => "integer",
                "label" => __("Repeat count", "post-expirator"),
                "description" => __("The number of times the scheduled action has been repeated.", "post-expirator"),
            ],
            [
                "name" => "repeat_limit",
                "type" => "integer",
                "label" => __("Repeat limit", "post-expirator"),
                "description" => __("The maximum number of times the scheduled action will be repeated.", "post-expirator"),
            ],
        ];
    }

    public function getOutputSchema(): array
    {
        $schema = [
            [
                "name" => "input",
                "type" => "input",
                "label" => __("Step input", "post-expirator"),
                "description" => __("The input data for this step.", "post-expirator"),
            ]
        ];

        $schema = array_merge($schema, $this->getStepScopedVariablesSchema());

        return $schema;
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
                    "label" => __("After delay", "post-expirator"),
                ],
                [
                    "id" => "finished",
                    "label" => __("After all repetitions", "post-expirator"),
                    "conditions" => [
                        "and" => [
                            [
                                "!=" => [
                                    ["var" => "schedule.recurrence"],
                                    "single"
                                ]
                            ],
                            [
                                "!=" => [
                                    ["var" => "schedule.repeatUntil"],
                                    "forever"
                                ]
                            ],
                            [
                                "!=" => [
                                    ["var" => "schedule"],
                                    null
                                ]
                            ],
                            [
                                "!=" => [
                                    ["var" => "schedule"],
                                    []
                                ]
                            ],
                        ]
                    ],
                ],
            ]
        ];
    }

    public function isProFeature(): bool
    {
        return false;
    }
}
