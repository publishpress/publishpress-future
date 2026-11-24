<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

\defined('ABSPATH') || exit;

interface DataTypeInterface
{
    public function getName(): string;

    public function getLabel(): string;

    public function getDescription(): string;
}
