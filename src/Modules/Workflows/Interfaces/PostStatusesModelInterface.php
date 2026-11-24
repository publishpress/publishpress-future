<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface PostStatusesModelInterface
{
    public function getPostStatuses(): array;

    public function getPostStatusesAsOptions(): array;
}
