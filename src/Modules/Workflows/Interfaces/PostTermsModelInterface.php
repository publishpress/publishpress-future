<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface PostTermsModelInterface
{
    public function getAllTerms(): array;

    public function getAllTermsAsOptions(): array;
}
