<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\WooSetProductStock;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;

class WooSetProductStockRunner implements StepRunnerInterface
{
    use WooExpressionResolverTrait;

    private const VALID_STOCK_STATUSES = ['instock', 'outofstock', 'onbackorder'];

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
        return WooSetProductStock::getNodeTypeName();
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

                $stockQuantity = $this->resolveExpressionField($nodeSettings, 'stockQuantity');

                $stockStatus = $nodeSettings['stockStatus'] ?? '';
                if (is_array($stockStatus)) {
                    $stockStatus = $stockStatus['value'] ?? '';
                }
                $stockStatus = is_string($stockStatus) ? trim($stockStatus) : '';

                if ($stockQuantity === '' && $stockStatus === '') {
                    $this->logger->debugWithArgs('No stock change provided, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                if ($stockQuantity !== '') {
                    $product->set_manage_stock(true);
                    $product->set_stock_quantity((int) $stockQuantity);
                }

                if ($stockStatus !== '' && in_array($stockStatus, self::VALID_STOCK_STATUSES, true)) {
                    $product->set_stock_status($stockStatus);
                }

                $product->save();

                $this->logger->debugWithArgs(
                    'Product stock updated | Product ID: %1$s | Slug: %2$s',
                    $product->get_id(),
                    $nodeSlug
                );
            }
        );
    }
}
