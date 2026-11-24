<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\InstanceProtection;

use PublishPress\Future\Core\Paths;
use PublishPress\Future\Framework\ModuleInterface;
use PublishPressInstanceProtection\Config;
use PublishPressInstanceProtection\InstanceChecker;

\defined('ABSPATH') || exit;

final class Module implements ModuleInterface
{
    private $pluginSlug;

    private $pluginName;

    public function __construct(Paths $paths, $pluginSlug, $pluginName)
    {
        $includeFile = $paths->getVendorDirPath()
            . '/publishpress/instance-protection/include.php';

        if (is_file($includeFile) && is_readable($includeFile)) {
            // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
            require_once $includeFile;
        }

        if (! class_exists('PublishPressInstanceProtection\\Config')) {
            return null;
        }

        $this->pluginSlug = $pluginSlug;
        $this->pluginName = $pluginName;
    }

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $pluginCheckerConfig = new Config();
        $pluginCheckerConfig->pluginSlug = $this->pluginSlug;
        $pluginCheckerConfig->pluginName = $this->pluginName;

        new InstanceChecker($pluginCheckerConfig);
    }
}
