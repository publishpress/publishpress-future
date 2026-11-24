<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface PostAuthorsModelInterface
{
    public function getAuthors(): array;

    public function getAuthorsAsOptions(): array;
}
