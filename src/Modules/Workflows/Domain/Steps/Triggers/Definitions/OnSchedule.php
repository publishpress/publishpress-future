<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

\defined('ABSPATH') || exit;

class OnSchedule implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "trigger/core.cron-schedule";
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
        return "onSchedule";
    }

    public function getLabel(): string
    {
        return __("On schedule", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This trigger allows you to run a workflow at a specific time, or relative to another date. You can also use this trigger to repeat workflows.", "post-expirator");
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
                "description" => __("Choose a schedule to activate the workflow.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "schedule",
                        "type" => "schedule",
                        "label" => __("Date offset", "post-expirator"),
                        "settings" => [
                            "hideDateSources" => [
                                "event",
                                "step",
                                "global.user.user_registered",
                            ],
                            "hidePreventDuplicateScheduling" => true,
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
                    "label" => __("When scheduled", "post-expirator"),
                ],
                [
                    "id" => "finished",
                    "label" => __("After all repetitions", "post-expirator"),
                    "conditions" => [
                        "and" => [
                            [
                                "!=" => [
                                    ["var" => "schedule.recurrence"],
                                    "single",
                                ],
                            ],
                            [
                                "!=" => [
                                    ["var" => "schedule.repeatUntil"],
                                    "forever",
                                ],
                            ],
                            [
                                "!=" => [
                                    ["var" => "schedule"],
                                    null,
                                ],
                            ],
                            [
                                "!=" => [
                                    ["var" => "schedule"],
                                    [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function isProFeature(): bool
    {
        return true;
    }
}
