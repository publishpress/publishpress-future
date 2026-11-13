<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class WooExpireCoupon implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.woo-coupon-expire";
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
        return "wooExpireCoupon";
    }

    public function getLabel(): string
    {
        return __("Expire a coupon", "post-expirator");
    }

    public function getDescription(): string
    {
        return __(
            "This step expires a WooCommerce coupon so it can no longer be used.",
            "post-expirator"
        );
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
        return "woocommerce";
    }

    public function getSettingsSchema(): array
    {
        return [
            [
                "label" => __("Coupon", "post-expirator"),
                "description" => __("The coupon to expire.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "coupon",
                        "type" => "expression",
                        "label" => __("Coupon code or ID", "post-expirator"),
                        "description" => __(
                            "The coupon code or ID to expire.",
                            "post-expirator"
                        ),
                    ],
                    [
                        "name" => "expiryDate",
                        "type" => "expression",
                        "label" => __("Expiry date (optional)", "post-expirator"),
                        "description" => __(
                            "A date (YYYY-MM-DD) to set as the coupon's expiry. "
                            . "Leave empty to expire the coupon immediately.",
                            "post-expirator"
                        ),
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
                        "field" => "coupon",
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
