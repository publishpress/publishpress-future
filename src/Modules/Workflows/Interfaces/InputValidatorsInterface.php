<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface InputValidatorsInterface
{
    public function validate(array $args): bool;
}
