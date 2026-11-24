<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Interfaces;

\defined('ABSPATH') || exit;

interface ExpirationActionInterface
{
    /**
     * @return bool
     */
    public function execute();

    /**
     * @return string
     */
    public function getNotificationText();

    /**
     * @return string
     */
    public function __toString();

    public static function getLabel(string $postType = ''): string;

    /**
     * @return string
     */
    public function getDynamicLabel($postType = '');
}
