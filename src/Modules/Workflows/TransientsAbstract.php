<?php

namespace PublishPress\Future\Modules\Workflows;

\defined('ABSPATH') || exit;

abstract class TransientsAbstract
{
    public const TRANSIENT_DUPLICATE_PREVENTION = 'ppfuture_workflow_execution_prevent_duplicate_';
}
