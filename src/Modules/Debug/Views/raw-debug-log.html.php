<?php

/**
 * Raw debug log view.
 *
 * Outputs the full log as plain text. Supports grouped by request or time sequence.
 * Used for both "Download" (attachment) and "View Full Log in New Tab" (inline).
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2025, PublishPress
 * @license GPLv2 or later
 */

header('Content-Type: text/plain; charset=utf-8');

if (! empty($raw_debug_log_disposition) && $raw_debug_log_disposition === 'attachment') {
    $filename = 'publishpress-future-debug-log-' . gmdate('Y-m-d-His') . '.txt';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
}

$results = $this->logger->fetchAll();
$totalLogs = $this->logger->getTotalLogs();
$logSizeInBytes = $this->logger->getLogSizeInBytes();

if (empty($results)) {
    echo 'No results found';
    exit;
}

echo sprintf(
    'Total logs: %d, Log size: %s',
    esc_html($totalLogs),
    esc_html(PostExpirator_Util::formatBytes($logSizeInBytes))
);
echo "\n\n";

$grouped = ! empty($raw_debug_log_grouped);
$separator = str_repeat('-', 60);
$previousRequestId = null;

foreach ($results as $result) {
    if ($grouped) {
        $requestId = isset($result['request_id']) && $result['request_id'] !== ''
            ? $result['request_id']
            : '(no request id)';
        if ($previousRequestId !== null && $previousRequestId !== $requestId) {
            echo $separator . "\n";
        }
        $previousRequestId = $requestId;
        $requestIdPrefix = $requestId !== '(no request id)'
            ? '[' . esc_html($requestId) . '] '
            : '';
    } else {
        $requestIdPrefix = isset($result['request_id']) && $result['request_id'] !== ''
            ? '[' . esc_html($result['request_id']) . '] '
            : '';
    }
    echo $requestIdPrefix . esc_html($result['timestamp']) . ': ' . esc_html($result['message']) . "\n";
}
