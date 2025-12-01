<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Debug\Controllers;

use PostExpirator_Util;
use PublishPress\Future\Core\HooksAbstract as CoreAbstractHooks;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Debug\HooksAbstract;

defined('ABSPATH') or die('Direct access not allowed.');

class Controller implements InitializableInterface
{
    /**
     * @var HooksFacade
     */
    private $hooks;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(HooksFacade $hooks, LoggerInterface $logger)
    {
        $this->hooks = $hooks;
        $this->logger = $logger;
    }

    public function initialize()
    {
        $this->hooks->addAction(HooksAbstract::ACTION_DEBUG_LOG, [$this, 'onDebugLog']);

        $this->hooks->addAction(
            CoreAbstractHooks::ACTION_DEACTIVATE_PLUGIN,
            [$this, 'onDeactivatePlugin']
        );

        $this->hooks->addAction(
            CoreAbstractHooks::ACTION_ADMIN_INIT,
            [$this, 'onDownloadLog']
        );
    }

    public function onDebugLog($message)
    {
        $this->logger->debug($message);
    }

    public function onDeactivatePlugin()
    {
        $preserveData = (bool)get_option('expirationdatePreserveData', 1);

        if (! $preserveData) {
            $this->logger->dropDatabaseTable();
        }
    }

    public function onDownloadLog()
    {
        if (! isset($_GET['action']) || $_GET['action'] !== 'publishpress_future_debug_log') {
            return;
        }

        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'post-expirator'), '', ['response' => 403]);
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if (! isset($_GET['nonce']) || ! wp_verify_nonce(sanitize_key($_GET['nonce']), 'publishpress_future_download_log')) {
            wp_die(esc_html__('Invalid nonce.', 'post-expirator'), '', ['response' => 403]);
        }

        require_once __DIR__ . '/../Views/raw-debug-log.html.php';

        exit;
    }
}
