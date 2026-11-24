<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Facade;

\defined('ABSPATH') || exit;

class SanitizationFacade
{
    /**
     * @param string $key
     * @return string
     */
    public function sanitizeKey($key)
    {
        return sanitize_key($key);
    }

    public function sanitizeTextField($value)
    {
        return sanitize_text_field($value);
    }
}
