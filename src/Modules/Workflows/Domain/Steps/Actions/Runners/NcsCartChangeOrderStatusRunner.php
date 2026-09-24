<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\NcsCartChangeOrderStatus;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;

class NcsCartChangeOrderStatusRunner implements StepRunnerInterface
{
    use NcsCartResolverTrait;

    private const VALID_STATUSES = ['pending-payment', 'paid', 'completed', 'refunded', 'failed'];

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
        return NcsCartChangeOrderStatus::getNodeTypeName();
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

                if (! function_exists('sc_setup_order')) {
                    $this->logger->debugWithArgs('PublishPress Cart not active, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                $nodeSettings = $this->stepProcessor->getNodeSettings(
                    $this->stepProcessor->getNodeFromStep($step)
                );

                $orderId = $this->resolveExpressionField($nodeSettings, 'orderId');
                if ($orderId === '' || ! ctype_digit($orderId)) {
                    $this->logger->debugWithArgs('No valid order ID, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }
                $orderId = (int) $orderId;

                if (get_post_type($orderId) !== 'sc_order') {
                    $this->logger->debugWithArgs(
                        'Post %1$s is not a Cart order, skipping | Slug: %2$s',
                        $orderId,
                        $nodeSlug
                    );
                    return;
                }

                $status = $nodeSettings['status'] ?? '';
                if (is_array($status)) {
                    $status = $status['value'] ?? '';
                }
                $status = is_string($status) ? trim($status) : '';

                if (! in_array($status, self::VALID_STATUSES, true)) {
                    $this->logger->debugWithArgs('Invalid status "%1$s", skipping | Slug: %2$s', $status, $nodeSlug);
                    return;
                }

                update_post_meta($orderId, '_sc_status', $status);

                $this->logger->debugWithArgs(
                    'Cart order %1$s status set to %2$s | Slug: %3$s',
                    $orderId,
                    $status,
                    $nodeSlug
                );
            }
        );
    }
}
