<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class NcsCartChangeOrderStatus implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.ncscart-order-change-status";
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
        return "ncsCartChangeOrderStatus";
    }

    public function getLabel(): string
    {
        return __("Change Cart order status", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step changes the status of a PublishPress Cart order.", "post-expirator");
    }

    public function getIcon(): string
    {
        return "cart";
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
        return "publishpress-cart";
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                "label" => __("Order", "post-expirator"),
                "description" => __("The order to update.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "orderId",
                        "type" => "expression",
                        "label" => __("Order ID", "post-expirator"),
                        "description" => __("The ID of the Cart order to update.", "post-expirator"),
                    ],
                    [
                        "name" => "status",
                        "type" => "select",
                        "label" => __("New status", "post-expirator"),
                        "settings" => [
                            "options" => [
                                ["value" => "pending-payment", "label" => __("Pending", "post-expirator")],
                                ["value" => "paid", "label" => __("Paid", "post-expirator")],
                                ["value" => "completed", "label" => __("Completed", "post-expirator")],
                                ["value" => "refunded", "label" => __("Refunded", "post-expirator")],
                                ["value" => "failed", "label" => __("Failed", "post-expirator")],
                            ],
                        ],
                        "default" => "completed",
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
                    ["rule" => "required", "field" => "orderId"],
                    ["rule" => "required", "field" => "status"],
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
