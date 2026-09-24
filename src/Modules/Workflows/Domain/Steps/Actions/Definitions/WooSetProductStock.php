<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class WooSetProductStock implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.woo-product-stock";
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
        return "wooSetProductStock";
    }

    public function getLabel(): string
    {
        return __("Set product stock", "post-expirator");
    }

    public function getDescription(): string
    {
        return __(
            "This step sets the stock quantity and/or stock status of a WooCommerce product.",
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
                "label" => __("Product", "post-expirator"),
                "description" => __("The product to update.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "product",
                        "type" => "expression",
                        "label" => __("Product ID or SKU", "post-expirator"),
                        "description" => __(
                            "The ID or SKU of the product to update.",
                            "post-expirator"
                        ),
                    ],
                ],
            ],
            [
                "label" => __("Stock", "post-expirator"),
                "description" => __(
                    "Set the stock quantity and/or the stock status. Leave the quantity empty to keep it unchanged.",
                    "post-expirator"
                ),
                "fields" => [
                    [
                        "name" => "stockQuantity",
                        "type" => "expression",
                        "label" => __("Stock quantity", "post-expirator"),
                        "description" => __(
                            "The new stock quantity. Setting this enables stock management for the product.",
                            "post-expirator"
                        ),
                    ],
                    [
                        "name" => "stockStatus",
                        "type" => "select",
                        "label" => __("Stock status", "post-expirator"),
                        "description" => __("The new stock status.", "post-expirator"),
                        "settings" => [
                            "options" => [
                                [
                                    "value" => "",
                                    "label" => __("— No change —", "post-expirator"),
                                ],
                                [
                                    "value" => "instock",
                                    "label" => __("In stock", "post-expirator"),
                                ],
                                [
                                    "value" => "outofstock",
                                    "label" => __("Out of stock", "post-expirator"),
                                ],
                                [
                                    "value" => "onbackorder",
                                    "label" => __("On backorder", "post-expirator"),
                                ],
                            ],
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
                        "field" => "product",
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
