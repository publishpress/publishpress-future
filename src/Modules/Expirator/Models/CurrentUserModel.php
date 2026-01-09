<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Models;

use PublishPress\Future\Framework\WordPress\Models\CurrentUserModel as FrameworkCurrentUserModel;
use PublishPress\Future\Modules\Expirator\CapabilitiesAbstract as Capabilities;

defined('ABSPATH') or die('Direct access not allowed.');

class CurrentUserModel extends FrameworkCurrentUserModel
{
    public function userCanExpirePosts()
    {
        $user = $this->getUserInstance();

        return is_object($user)
            && $user->has_cap(Capabilities::EXPIRE_POST);
    }

    public function userCanEditPost($postId)
    {
        return current_user_can('edit_post', $postId);
    }

    public function userCanReadPost($postId)
    {
        return current_user_can('read_post', $postId);
    }
}
