<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Models;

defined('ABSPATH') or die('Direct access not allowed.');

class CurrentUserModel extends UserModel
{
    /**
     * Parent requires a user id, but this model always reads the live current user.
     */
    public function __construct()
    {
        parent::__construct(0);
    }

    /**
     * Always return the live current user. Do not cache a constructor snapshot.
     *
     * Application Password auth runs after plugin init, so a cached WP_User
     * created during REST route registration would stay a guest (ID 0).
     *
     * @return \WP_User
     */
    public function getUserInstance()
    {
        return wp_get_current_user();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        $user = $this->getUserInstance();

        if (! is_object($user)) {
            return 0;
        }

        return (int) $user->ID;
    }
}
