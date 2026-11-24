<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface PostTypesModelInterface
{
    public function getPostTypes(): array;

    public function getPostTypesAsOptions(): array;
}
