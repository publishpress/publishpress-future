<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\UnstickPost;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;

class UnstickPostRunner implements StepRunnerInterface
{
    /**
     * @var StepProcessorInterface
     */
    private $stepProcessor;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StepProcessorInterface $stepProcessor,
        \Closure $expirablePostModelFactory,
        LoggerInterface $logger
    ) {
        $this->stepProcessor = $stepProcessor;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->logger = $logger;
    }

    public static function getNodeTypeName(): string
    {
        return UnstickPost::getNodeTypeName();
    }

    public function setup(array $step): void
    {
        $this->stepProcessor->setup($step, [$this, 'actionCallback']);
    }

    public function actionCallback(int $postId, array $nodeSettings, array $step)
    {
        $this->stepProcessor->executeSafelyWithErrorHandling(
            $step,
            function ($step, $postId) {
                $nodeSlug = $this->stepProcessor->getSlugFromStep($step);

                $unstickUsingTheModel = true;

                // phpcs:disable WordPress.Security.NonceVerification.Missing
                $isQuickEdit = defined('DOING_AJAX')
                    && DOING_AJAX
                    && isset($_POST['action'])
                    && $_POST['action'] === 'inline-save';

                $isClassicEditor = ( ! defined('DOING_AJAX') || ! DOING_AJAX)
                    && isset($_POST['action'])
                    && $_POST['action'] === 'editpost';
                // phpcs:enable WordPress.Security.NonceVerification.Missing

                /*
                 * Handle quick-edit or classic editor saving, otherwise it will override
                 * the sticky status at the end of the save.
                 *
                 * @see https://github.com/publishpress/PublishPress-Future/issues/1204
                 */
                if ($isQuickEdit || $isClassicEditor) {
                    $_POST['sticky'] = false;

                    $unstickUsingTheModel = false;

                    $this->logger->debugWithArgs(
                        'Post %1$s unsticked on %2$s setting sticky status via POST',
                        $postId,
                        $nodeSlug
                    );
                }

                if ($unstickUsingTheModel) {
                    $postModel = call_user_func($this->expirablePostModelFactory, $postId);
                    $postModel->unstick();

                    $this->logger->debugWithArgs('Post %1$s unsticked on step %2$s using the model', $postId, $nodeSlug);
                }
            },
            $postId
        );
    }
}
