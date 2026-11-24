<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface TriggerRunnerInterface
{
    public function setup(int $workflowId, array $step): void;
}
