<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine;

use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextProcessorRegistryInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextProcessorInterface;

\defined('ABSPATH') || exit;

class ExecutionContextProcessorRegistry implements ExecutionContextProcessorRegistryInterface
{
    private array $processors = [];

    public function register(ExecutionContextProcessorInterface $processor): void
    {
        $this->processors[$processor->getType()] = $processor;
    }

    public function process(string $type, $value, array $parameters = [])
    {
        if (!$this->hasProcessor($type)) {
            throw new \InvalidArgumentException(
                sprintf('Execution context processor "%s" not found', esc_html($type))
            );
        }

        /**
         * @var ExecutionContextProcessorInterface $processor
         */
        $processor = $this->processors[$type];

        return $processor->process($value, $parameters);
    }

    public function hasProcessor(string $type): bool
    {
        return isset($this->processors[$type]);
    }
}
