<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Core;

\defined('ABSPATH') || exit;

class Paths
{
    /**
     * @var string
     */
    private $baseDir;

    public function __construct($baseDir)
    {
        $this->baseDir = (string) $baseDir;
    }

    public function getVendorDirPath()
    {
        return PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH;
    }

    public function getBaseDirPath()
    {
        return rtrim($this->baseDir, '/');
    }
}
