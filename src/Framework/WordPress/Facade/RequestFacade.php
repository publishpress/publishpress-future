<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Facade;

\defined('ABSPATH') || exit;

class RequestFacade
{
    /**
     * @param string $action
     * @param string $query_arg
     * @return false|int|void
     */
    public function checkAdminReferer($action = -1, $query_arg = '_wpnonce')
    {
        return check_admin_referer($action, $query_arg);
    }
}
