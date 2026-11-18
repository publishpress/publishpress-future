<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\WooSetProductSalePrice;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;

class WooSetProductSalePriceRunner implements StepRunnerInterface
{
    use WooExpressionResolverTrait;

    /**
     * @var StepProcessorInterface
     */
    private $stepProcessor;

    /**
     * @var ExecutionContextInterface
     */
    private $executionContext;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StepProcessorInterface $stepProcessor,
        ExecutionContextInterface $executionContext,
        LoggerInterface $logger
    ) {
        $this->stepProcessor = $stepProcessor;
        $this->executionContext = $executionContext;
        $this->logger = $logger;
    }

    public static function getNodeTypeName(): string
    {
        return WooSetProductSalePrice::getNodeTypeName();
    }

    public function setup(array $step): void
    {
        $this->stepProcessor->setup($step, [$this, 'setupCallback']);
    }

    public function setupCallback(array $step)
    {
        $this->stepProcessor->executeSafelyWithErrorHandling(
            $step,
            function ($step) {
                $nodeSlug = $this->stepProcessor->getSlugFromStep($step);

                if (! function_exists('wc_get_product')) {
                    $this->logger->debugWithArgs('WooCommerce not active, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                $nodeSettings = $this->stepProcessor->getNodeSettings(
                    $this->stepProcessor->getNodeFromStep($step)
                );

                $productRef = $this->resolveExpressionField($nodeSettings, 'product');
                $productId = $this->resolveProductId($productRef);

                $product = $productId > 0 ? wc_get_product($productId) : false;
                if (! $product) {
                    $this->logger->debugWithArgs(
                        'Product %1$s not found | Slug: %2$s',
                        $productRef,
                        $nodeSlug
                    );
                    return;
                }

                $regularPrice = $this->resolveExpressionField($nodeSettings, 'regularPrice');
                $salePrice = $this->resolveExpressionField($nodeSettings, 'salePrice');

                if ($regularPrice === '' && $salePrice === '') {
                    $this->logger->debugWithArgs('No price provided, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                if ($regularPrice !== '') {
                    $product->set_regular_price($regularPrice);
                }

                if ($salePrice !== '') {
                    // A sale price of "0" (or empty) clears the sale.
                    $product->set_sale_price($salePrice === '0' ? '' : $salePrice);
                }

                $product->save();

                $this->logger->debugWithArgs(
                    'Product price updated | Product ID: %1$s | Slug: %2$s',
                    $product->get_id(),
                    $nodeSlug
                );
            }
        );
    }
}
