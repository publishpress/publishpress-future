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
use PublishPress\Future\Modules\Workflows\HooksAbstract as WorkflowsHooksAbstract;

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

        if (! defined('PUBLISHPRESS_FUTURE_DEBUG_EXECUTION_CONTEXT')) {
            define('PUBLISHPRESS_FUTURE_DEBUG_EXECUTION_CONTEXT', false);
        }
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

        $this->hooks->addAction(
            WorkflowsHooksAbstract::ACTION_WORKFLOW_TRIGGER_EXECUTED,
            [$this, 'onWorkflowTriggerExecuted']
        );

        $this->hooks->addAction(
            CoreAbstractHooks::ACTION_SHUTDOWN,
            [$this, 'onShutdown'],
            PHP_INT_MAX
        );
    }

    public function onShutdown()
    {
        $this->logger->debug('Shutdown');
    }

    /**
     * Mark the current request in the debug log when a workflow trigger is executed.
     *
     * @since 4.10.0
     * @return void
     */
    public function onWorkflowTriggerExecuted(): void
    {
        $this->logger->markCurrentRequestHasTriggerActivated();
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
        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : '';

        if ($action !== 'publishpress_future_debug_log') {
            return;
        }

        if (! current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'post-expirator'), '', ['response' => 403]);
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        if (! isset($_GET['nonce']) || ! wp_verify_nonce(sanitize_key($_GET['nonce']), 'publishpress_future_download_log')) {
            wp_die(esc_html__('Invalid nonce.', 'post-expirator'), '', ['response' => 403]);
        }

        $grouped = isset($_GET['grouped']) ? (int)$_GET['grouped'] : 0;
        $disposition = isset($_GET['disposition']) ? sanitize_key($_GET['disposition']) : 'inline';
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $triggerActivatedOnly = isset($_GET['trigger_activated_only']) ? (int)$_GET['trigger_activated_only'] : 0;

        // Variables for the view.
        $rawDebugLogGrouped = (bool)$grouped;
        $rawDebugLogDisposition = $disposition === 'attachment' ? 'attachment' : 'inline';
        $rawDebugLogTriggerActivatedOnly = (bool)$triggerActivatedOnly;

        require_once __DIR__ . '/../Views/raw-debug-log.html.php';

        exit;
    }
}
