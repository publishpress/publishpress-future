<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface ExecutionContextProcessorInterface
{
    public function getType(): string;

    public function process(string $value, array $parameters);
}
