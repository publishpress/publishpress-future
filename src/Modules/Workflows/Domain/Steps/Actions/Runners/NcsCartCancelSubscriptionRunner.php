<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\NcsCartCancelSubscription;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;

class NcsCartCancelSubscriptionRunner implements StepRunnerInterface
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
        return NcsCartCancelSubscription::getNodeTypeName();
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

                if (! function_exists('sc_do_cancel_subscription')) {
                    $this->logger->debugWithArgs('PublishPress Cart not active, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }

                $nodeSettings = $this->stepProcessor->getNodeSettings(
                    $this->stepProcessor->getNodeFromStep($step)
                );

                $subscriptionId = $this->resolveExpressionField($nodeSettings, 'subscriptionId');
                if ($subscriptionId === '' || ! ctype_digit($subscriptionId)) {
                    $this->logger->debugWithArgs('No valid subscription ID, skipping | Slug: %1$s', $nodeSlug);
                    return;
                }
                $subscriptionId = (int) $subscriptionId;

                if (get_post_type($subscriptionId) !== 'sc_subscription') {
                    $this->logger->debugWithArgs(
                        'Post %1$s is not a Cart subscription, skipping | Slug: %2$s',
                        $subscriptionId,
                        $nodeSlug
                    );
                    return;
                }

                $when = $nodeSettings['when'] ?? 'now';
                if (is_array($when)) {
                    $when = $when['value'] ?? 'now';
                }
                $cancelNow = ($when !== 'period_end');

                // sc_do_cancel_subscription($sub, $sub_id, $now, $echo, $options)
                // A numeric first arg is turned into an NCS_Cart_Subscription internally.
                // $echo = false so nothing is printed during workflow execution.
                sc_do_cancel_subscription($subscriptionId, false, $cancelNow, false);

                $this->logger->debugWithArgs(
                    'Cart subscription %1$s cancelled (%2$s) | Slug: %3$s',
                    $subscriptionId,
                    $cancelNow ? 'immediately' : 'at period end',
                    $nodeSlug
                );
            }
        );
    }
}
