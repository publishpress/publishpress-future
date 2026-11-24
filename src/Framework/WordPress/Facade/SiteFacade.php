<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Facade;

\defined('ABSPATH') || exit;

class SiteFacade
{
    /**
     * @return int
     */
    public function getBlogId()
    {
        if ($this->isMultisite()) {
            global $current_blog;

            return (int) $current_blog->blog_id;
        }

        return 0;
    }

    /**
     * @return bool
     */
    public function isMultisite()
    {
        return \is_multisite();
    }
}
