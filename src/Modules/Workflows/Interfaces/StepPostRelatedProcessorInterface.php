<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface StepPostRelatedProcessorInterface extends StepProcessorInterface
{
    public function setPostIdOnTriggerGlobalVariable(int $postId): void;
}
