<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework;

\defined('ABSPATH') || exit;

interface InitializableInterface
{
    /**
     * @return void
     */
    public function initialize();
}
