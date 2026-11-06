<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\WooExpireCoupon;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;
use WC_Coupon;

class WooExpireCouponRunner implements StepRunnerInterface
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
        return WooExpireCoupon::getNodeTypeName();
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

                if (! class_exists('WC_Coupon')) {
                    $this->logger->debugWithArgs('WooCommerce not active, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                $nodeSettings = $this->stepProcessor->getNodeSettings(
                    $this->stepProcessor->getNodeFromStep($step)
                );

                $couponRef = $this->resolveExpressionField($nodeSettings, 'coupon');
                if ($couponRef === '') {
                    $this->logger->debugWithArgs('No coupon specified, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                $coupon = new WC_Coupon($couponRef);
                if (! $coupon->get_id()) {
                    $this->logger->debugWithArgs(
                        'Coupon %1$s not found | Slug: %2$s',
                        $couponRef,
                        $nodeSlug
                    );
                    return;
                }

                $expiryDate = $this->resolveExpressionField($nodeSettings, 'expiryDate');
                if ($expiryDate !== '') {
                    $timestamp = strtotime($expiryDate);
                    if ($timestamp === false) {
                        $this->logger->debugWithArgs(
                            'Invalid expiry date "%1$s", skipping | Slug: %2$s',
                            $expiryDate,
                            $nodeSlug
                        );
                        return;
                    }
                } else {
                    // Expire immediately: set expiry to yesterday so WooCommerce treats it as expired.
                    $timestamp = time() - DAY_IN_SECONDS;
                }

                $coupon->set_date_expires($timestamp);
                $coupon->save();

                $this->logger->debugWithArgs(
                    'Coupon %1$s expiry set | Coupon ID: %2$s | Slug: %3$s',
                    $couponRef,
                    $coupon->get_id(),
                    $nodeSlug
                );
            }
        );
    }
}
