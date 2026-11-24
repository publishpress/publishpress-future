<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Facade;

use WP_Error;

\defined('ABSPATH') || exit;

class ErrorFacade
{
    /**
     * @param mixed $value
     * @return bool
     */
    public function isWpError($value)
    {
        return \is_wp_error($value);
    }

    /**
     * @param WP_Error $error
     * @return string
     */
    public function getWpErrorMessage($error)
    {
        return $error->get_error_message();
    }
}
