<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextRegistryInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextProcessorRegistryInterface;

class ExecutionContextRegistry implements ExecutionContextRegistryInterface
{
    private $executionContexts = [];

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var ExecutionContextProcessorRegistryInterface
     */
    private $executionContextProcessorRegistry;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        HookableInterface $hooks,
        ExecutionContextProcessorRegistryInterface $executionContextProcessorRegistry,
        \Closure $expirablePostModelFactory,
        LoggerInterface $logger
    ) {
        $this->hooks = $hooks;
        $this->executionContextProcessorRegistry = $executionContextProcessorRegistry;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->logger = $logger;
    }

    public function getExecutionContext(string $workflowExecutionId): ExecutionContextInterface
    {
        if (!isset($this->executionContexts[$workflowExecutionId])) {
            $this->executionContexts[$workflowExecutionId] = new ExecutionContext(
                $this->hooks,
                $this->executionContextProcessorRegistry,
                $this->expirablePostModelFactory,
                $this->logger
            );
        }

        return $this->executionContexts[$workflowExecutionId];
    }

    public function removeExecutionContext(string $workflowExecutionId): void
    {
        unset($this->executionContexts[$workflowExecutionId]);
    }
}
