<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Debug;

use PublishPress\Future\Framework\Logger\LoggerInterface;

\defined('ABSPATH') || exit;

/**
 * @deprecated 4.1.0 Use the logger facade instead.
 */
class Debug implements DebugInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \PublishPress\Future\Modules\Settings\SettingsFacade
     */
    private $settings;

    public function __construct(LoggerInterface $logger, $settings)
    {
        $this->logger = $logger;
        $this->settings = $settings;
    }

    /**
     * @inheritDoc
     */
    public function log($message)
    {
        if ($this->isEnabled()) {
            $this->logger->debug($message);
        }
    }

    /**
     * @inheritDoc
     */
    public function dropDatabaseTable()
    {
        $this->logger->dropDatabaseTable();
    }

    /**
     * @inheritDoc
     */
    public function fetchAll()
    {
        return $this->logger->fetchAll();
    }

    /**
     * @inheritDoc
     */
    public function fetchLatest($limit = 100)
    {
        return $this->logger->fetchLatest($limit);
    }

    /**
     * @inheritDoc
     */
    public function deleteLogs()
    {
        $this->logger->deleteLogs();
    }

    /**
     * @inheritDoc
     */
    public function isEnabled()
    {
        return $this->settings->getDebugIsEnabled();
    }
}
