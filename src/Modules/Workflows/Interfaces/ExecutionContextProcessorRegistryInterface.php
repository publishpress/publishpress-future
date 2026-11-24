<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface ExecutionContextProcessorRegistryInterface
{
    /**
     * Registers a new processor
     *
     * @param ExecutionContextProcessorInterface $processor
     * @return void
     */
    public function register(ExecutionContextProcessorInterface $processor): void;

    /**
     * Executes a registered processor
     *
     * @param string $type The processor type
     * @param mixed $value The value to process
     * @param array $parameters Parameters to pass to the processor
     * @return mixed The result of the processor
     * @throws \InvalidArgumentException When processor is not found
     */
    public function process(string $type, $value, array $parameters = []);

    /**
     * Checks if a processor exists
     *
     * @param string $type The processor type
     * @return bool
     */
    public function hasProcessor(string $type): bool;
}
