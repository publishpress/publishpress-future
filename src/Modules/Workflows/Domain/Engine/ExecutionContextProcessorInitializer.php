<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine;

use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextProcessorRegistryInterface;

\defined('ABSPATH') || exit;

class ExecutionContextProcessorInitializer implements InitializableInterface
{
    /**
     * @var ExecutionContextProcessorRegistryInterface
     */
    private $processorRegistry;

    /**
     * @var array
     */
    private $processors;

    public function __construct(ExecutionContextProcessorRegistryInterface $processorRegistry, array $processors)
    {
        $this->processorRegistry = $processorRegistry;
        $this->processors = $processors;
    }

    public function initialize(): void
    {
        foreach ($this->processors as $processor) {
            $this->processorRegistry->register($processor);
        }
    }
}
