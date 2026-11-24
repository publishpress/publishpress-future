<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Models;

\defined('ABSPATH') || exit;

class UserModel
{
    /**
     * @var int
     */
    private $userId;

    /**
     * @var \WP_User
     */
    private $userInstance;

    /**
     * @param \WP_User|int $user
     */
    public function __construct($user)
    {
        if (is_object($user)) {
            $this->userInstance = $user;
            $this->userId = $user->ID;
        }

        if (is_numeric($user)) {
            $this->userId = (int) $user;
        }
    }

    public function getUserInstance()
    {
        if (empty($this->userInstance)) {
            $this->userInstance = get_user_by('ID', $this->userId);
        }

        return $this->userInstance;
    }

    /**
     * @param string $capability
     * @return bool
     */
    public function hasCapability($capability)
    {
        $userInstance = $this->getUserInstance();

        return $userInstance->has_cap($capability);
    }

    public function getId(): int
    {
        return $this->userId;
    }
}
