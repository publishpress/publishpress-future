<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class WooSetProductSalePrice implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.woo-product-sale-price";
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
        return "wooSetProductSalePrice";
    }

    public function getLabel(): string
    {
        return __("Set product price", "post-expirator");
    }

    public function getDescription(): string
    {
        return __(
            "This step sets the regular and/or sale price of a WooCommerce product.",
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
                "label" => __("Price", "post-expirator"),
                "description" => __(
                    "Set the regular price, the sale price, or both. Leave a field empty to keep it unchanged. "
                    . "Set the sale price to \"0\" to clear an existing sale.",
                    "post-expirator"
                ),
                "fields" => [
                    [
                        "name" => "regularPrice",
                        "type" => "expression",
                        "label" => __("Regular price", "post-expirator"),
                        "description" => __("The new regular price.", "post-expirator"),
                    ],
                    [
                        "name" => "salePrice",
                        "type" => "expression",
                        "label" => __("Sale price", "post-expirator"),
                        "description" => __("The new sale price.", "post-expirator"),
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
