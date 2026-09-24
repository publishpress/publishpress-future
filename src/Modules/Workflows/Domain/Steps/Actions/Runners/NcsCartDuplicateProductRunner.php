<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\IntegerResolver;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\NcsCartDuplicateProduct;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;

class NcsCartDuplicateProductRunner implements StepRunnerInterface
{
    use NcsCartResolverTrait;

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
        return NcsCartDuplicateProduct::getNodeTypeName();
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

                if (! class_exists('NCS_Cart_Product_Duplicator')) {
                    $this->logger->debugWithArgs('PublishPress Cart not active, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                $nodeSettings = $this->stepProcessor->getNodeSettings(
                    $this->stepProcessor->getNodeFromStep($step)
                );

                $productId = $this->resolveExpressionField($nodeSettings, 'productId');
                if ($productId === '' || ! ctype_digit($productId)) {
                    $this->logger->debugWithArgs('No valid product ID, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }
                $productId = (int) $productId;

                $newProductId = \NCS_Cart_Product_Duplicator::duplicate($productId);

                if (! is_numeric($newProductId) || (int) $newProductId <= 0) {
                    $this->logger->debugWithArgs(
                        'Failed to duplicate product %1$s | Slug: %2$s',
                        $productId,
                        $nodeSlug
                    );
                    return;
                }

                $this->executionContext->setVariable($nodeSlug, [
                    'productId' => new IntegerResolver((int) $newProductId),
                ]);

                $this->logger->debugWithArgs(
                    'Cart product %1$s duplicated to %2$s | Slug: %3$s',
                    $productId,
                    (int) $newProductId,
                    $nodeSlug
                );
            }
        );
    }
}
