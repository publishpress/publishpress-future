<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface WorkflowsModelInterface
{
    public function getPublishedWorkflowsIds(): array;

    public function hasCreatedSampleWorkflows(): bool;

    public function createSampleWorkflows(array $samples): void;
}
