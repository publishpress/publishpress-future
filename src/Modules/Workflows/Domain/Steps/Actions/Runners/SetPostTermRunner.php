<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Runners;

use PublishPress\Future\Framework\WordPress\Facade\ErrorFacade;
use PublishPress\Future\Modules\Workflows\Interfaces\StepRunnerInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\SetPostTerm;
use PublishPress\Future\Modules\Workflows\Interfaces\StepProcessorInterface;

class SetPostTermRunner implements StepRunnerInterface
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
     * @var ErrorFacade
     */
    private $errorFacade;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        StepProcessorInterface $stepProcessor,
        \Closure $expirablePostModelFactory,
        ErrorFacade $errorFacade,
        LoggerInterface $logger
    ) {
        $this->stepProcessor = $stepProcessor;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->errorFacade = $errorFacade;
        $this->logger = $logger;
    }

    public static function getNodeTypeName(): string
    {
        return SetPostTerm::getNodeTypeName();
    }

    public function setup(array $step): void
    {
        $this->stepProcessor->setup($step, [$this, 'setupCallback']);
    }

    public function setupCallback(int $postId, array $nodeSettings, array $step)
    {
        $this->stepProcessor->executeSafelyWithErrorHandling(
            $step,
            function ($step, $postId, $nodeSettings) {
                $postModel = call_user_func($this->expirablePostModelFactory, $postId);

                $taxonomy = $nodeSettings['taxonomyTerms']['taxonomy'];
                $updatedTerms = $nodeSettings['taxonomyTerms']['terms'] ?? [];

                $result = $postModel->setTerms($updatedTerms, $taxonomy);

                $resultIsError = $this->errorFacade->isWpError($result);

                $nodeSlug = $this->stepProcessor->getSlugFromStep($step);

                if ($resultIsError) {
                    $this->logger->errorWithArgs('Error updating post %1$s terms on step %2$s', $postId, $nodeSlug);
                } else {
                    $this->logger->debugWithArgs('Post %1$s terms updated on step %2$s', $postId, $nodeSlug);
                }
            },
            $postId,
            $nodeSettings
        );
    }
}
