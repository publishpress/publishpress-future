<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface TaxonomiesModelInterface
{
    public function getTaxonomies(): array;

    public function getTaxonomiesAsOptions(): array;
}
