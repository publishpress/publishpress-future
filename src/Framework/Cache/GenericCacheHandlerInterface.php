<?php

namespace PublishPress\Future\Framework\Cache;

\defined('ABSPATH') || exit;

interface GenericCacheHandlerInterface
{
    public function addValue(string $value): void;

    public function hasValue(string $value): bool;
}
