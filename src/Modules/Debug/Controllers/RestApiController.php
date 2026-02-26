<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Debug\Controllers;

use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Debug\DebugLogDisplayHelper;
use WP_REST_Request;
use WP_REST_Response;

defined('ABSPATH') or die('Direct access not allowed.');

class RestApiController implements InitializableInterface
{
    /**
     * @var HooksFacade
     */
    private $hooks;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param HooksFacade $hooks
     * @param LoggerInterface $logger
     */
    public function __construct(HooksFacade $hooks, LoggerInterface $logger)
    {
        $this->hooks = $hooks;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        $this->hooks->addAction('rest_api_init', [$this, 'handleRestAPIInit']);
    }

    /**
     * Registers the debug log REST API route.
     *
     * @return void
     */
    public function handleRestAPIInit(): void
    {
        register_rest_route('publishpress-future/v1', '/debug-log', [
            'methods' => 'GET',
            'callback' => [$this, 'getDebugLog'],
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
            'args' => [
                'log_count' => [
                    'default' => 500,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function ($param) {
                        return in_array((int) $param, [500, 700, 1000, 2500, 5000, 7500, 10000], true);
                    },
                ],
                'group_by_request' => [
                    'default' => 1,
                    'sanitize_callback' => 'absint',
                    'validate_callback' => function ($param) {
                        return in_array((int) $param, [0, 1], true);
                    },
                ],
                'trigger_activated_only' => [
                    'default' => 0,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ]);
    }

    /**
     * Returns the formatted debug log data.
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function getDebugLog(WP_REST_Request $request): WP_REST_Response
    {
        $logCount = (int) $request->get_param('log_count');
        $groupByRequest = (bool) $request->get_param('group_by_request');
        $triggerActivatedOnly = (bool) $request->get_param('trigger_activated_only');

        $results = $this->logger->fetchLatest($logCount, $triggerActivatedOnly);
        $totalLogs = $this->logger->getTotalLogs($triggerActivatedOnly);
        $totalLogsUnfiltered = $triggerActivatedOnly ? $this->logger->getTotalLogs(false) : $totalLogs;
        $logSizeInBytes = $this->logger->getLogSizeInBytes($triggerActivatedOnly);
        $logSizeBytesTotal = $triggerActivatedOnly ? $this->logger->getLogSizeInBytes(false) : $logSizeInBytes;

        $uniqueRequestIds = array_unique(array_filter(array_column($results, 'request_id')));
        $sessionCount = count($uniqueRequestIds);

        if ($groupByRequest) {
            $results = DebugLogDisplayHelper::sortResultsByRequest($results);
        }

        $logText = $this->formatLogText($results, $groupByRequest);

        return new WP_REST_Response([
            'log_text' => $logText,
            'total_logs' => $totalLogs,
            'total_logs_unfiltered' => $totalLogsUnfiltered,
            'total_displayed' => count($results),
            'session_count' => $sessionCount,
            'log_size_bytes' => $logSizeInBytes,
            'log_size_bytes_total' => $logSizeBytesTotal,
        ]);
    }

    /**
     * Formats the log results into a plain-text string.
     *
     * @param array $results
     * @param bool $groupByRequest
     * @return string
     */
    private function formatLogText(array $results, bool $groupByRequest): string
    {
        if (empty($results)) {
            return '';
        }

        $separator = str_repeat('-', 60);
        $previousRequestId = null;
        $output = '';

        foreach ($results as $result) {
            if ($groupByRequest) {
                $requestId = isset($result['request_id']) && $result['request_id'] !== ''
                    ? $result['request_id']
                    : '(no request id)';
                if ($previousRequestId !== null && $previousRequestId !== $requestId) {
                    $output .= $separator . "\n";
                }
                $isNewGroup = $previousRequestId === null || $previousRequestId !== $requestId;
                if ($isNewGroup) {
                    $output .= 'Request ID: ' . esc_html($requestId) . "\n";
                }
                $previousRequestId = $requestId;
                $requestIdPrefix = $requestId !== '(no request id)'
                    ? '[' . $requestId . '] '
                    : '';
            } else {
                $requestIdPrefix = isset($result['request_id']) && $result['request_id'] !== ''
                    ? '[' . $result['request_id'] . '] '
                    : '';
            }
            $output .= sprintf("%s: %s\n", $result['timestamp'], $result['message']);
        }

        return $output;
    }
}
