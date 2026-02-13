<?php

header('Content-Type: text/plain');

$results = $this->logger->fetchAll();
$totalLogs = $this->logger->getTotalLogs();
$logSizeInBytes = $this->logger->getLogSizeInBytes();

if (empty($results)) {
    echo 'No results found';
}

echo sprintf(
    'Total logs: %d, Log size: %s',
    esc_html($totalLogs),
    esc_html(PostExpirator_Util::formatBytes($logSizeInBytes))
);
echo "\n\n";

foreach ($results as $result) {
    $requestId = isset($result['request_id']) && $result['request_id'] !== ''
        ? '[' . esc_html($result['request_id']) . '] '
        : '';
    echo $requestId . esc_html($result['timestamp']) . ': ' . esc_html($result['message']) . "\n";
}
