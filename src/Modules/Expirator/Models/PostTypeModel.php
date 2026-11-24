<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Models;

\defined('ABSPATH') || exit;

class PostTypeModel
{
    /**
     * @var \WP_Post_Type
     */
    private $postType;

    public function load($postType)
    {
        $postType = get_post_type_object($postType);

        if (!$postType) {
            return false;
        }

        $this->postType = $postType;

        return true;
    }

    public function getPostType()
    {
        if (empty($this->postType)) {
            return '';
        }

        return $this->postType->name;
    }

    public function getLabel()
    {
        if (empty($this->postType)) {
            return '';
        }

        return $this->postType->label;
    }

    public function getSingularLabel()
    {
        if (empty($this->postType)) {
            return '';
        }

        return $this->postType->labels->singular_name;
    }
}
