<?php

use PublishPress\Future\Modules\Debug\DebugLogDisplayHelper;

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

if (! empty($rawDebugLogDisposition) && $rawDebugLogDisposition === 'attachment') {
    $filename = 'publishpress-future-debug-log-' . gmdate('Y-m-d-His') . '.txt';
    header('Content-Disposition: attachment; filename="' . $filename . '"');
}

$filterApplied = ! empty($rawDebugLogTriggerActivatedOnly);
$results = $this->logger->fetchAll($filterApplied);
$totalLogs = count($results);
$logSizeInBytes = $this->logger->getLogSizeInBytes($filterApplied);
$logSizeInBytesTotal = $filterApplied ? $this->logger->getLogSizeInBytes(false) : $logSizeInBytes;
$totalLogsUnfiltered = $filterApplied ? $this->logger->getTotalLogs(false) : $totalLogs;

$uniqueRequestIds = array_unique(array_filter(array_column($results, 'request_id')));
$sessionCount = count($uniqueRequestIds);

if (empty($results)) {
    echo 'No results found';
    exit;
}

echo $filterApplied
    ? sprintf(
        'Total logs: %s, Sessions: %s, Total in database: %s, Log size: %s (total: %s)',
        esc_html((string) $totalLogs),
        esc_html((string) $sessionCount),
        esc_html((string) $totalLogsUnfiltered),
        esc_html(PostExpirator_Util::formatBytes($logSizeInBytes)),
        esc_html(PostExpirator_Util::formatBytes($logSizeInBytesTotal))
    )
    : sprintf(
        'Total logs: %s, Sessions: %s, Log size: %s',
        esc_html((string) $totalLogs),
        esc_html((string) $sessionCount),
        esc_html(PostExpirator_Util::formatBytes($logSizeInBytes))
    );
echo "\n\n";

$grouped = ! empty($rawDebugLogGrouped);
$separator = str_repeat('-', 60);
$previousRequestId = null;

if ($grouped) {
    $results = DebugLogDisplayHelper::sortResultsByRequest($results);
}

foreach ($results as $result) {
    if ($grouped) {
        $requestId = isset($result['request_id']) && $result['request_id'] !== ''
            ? $result['request_id']
            : '(no request id)';
        if ($previousRequestId !== null && $previousRequestId !== $requestId) {
            echo esc_html($separator) . "\n";
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
    echo esc_html($requestIdPrefix) . esc_html($result['timestamp']) . ': ' . esc_html($result['message']) . "\n";
}
