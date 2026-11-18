<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions;

use PublishPress\Future\Modules\Workflows\Interfaces\StepTypeInterface;
use PublishPress\Future\Modules\Workflows\Models\StepTypesModel;

class NcsCartDuplicateProduct implements StepTypeInterface
{
    public static function getNodeTypeName(): string
    {
        return "action/core.ncscart-product-duplicate";
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
        return "ncsCartDuplicateProduct";
    }

    public function getLabel(): string
    {
        return __("Duplicate Cart product", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("This step duplicates a PublishPress Cart product.", "post-expirator");
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
                "label" => __("Product", "post-expirator"),
                "description" => __("The product to duplicate.", "post-expirator"),
                "fields" => [
                    [
                        "name" => "productId",
                        "type" => "expression",
                        "label" => __("Product ID", "post-expirator"),
                        "description" => __("The ID of the Cart product to duplicate.", "post-expirator"),
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
                    ["rule" => "required", "field" => "productId"],
                ],
            ],
        ];
    }

    public function getStepScopedVariablesSchema(): array
    {
        return [
            [
                "name" => "productId",
                "type" => "integer",
                "label" => __("Duplicated product ID", "post-expirator"),
                "description" => __("The ID of the newly created product.", "post-expirator"),
            ],
        ];
    }

    public function getOutputSchema(): array
    {
        return array_merge(
            [
                [
                    "name" => "input",
                    "type" => "input",
                    "label" => __("Step input", "post-expirator"),
                    "description" => __("The input data for this step.", "post-expirator"),
                ],
            ],
            $this->getStepScopedVariablesSchema()
        );
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
