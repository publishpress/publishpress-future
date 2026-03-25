<?php

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Debug\DebugLogDisplayHelper;
use PublishPress\Future\Modules\Settings\HooksAbstract;

$container = Container::getInstance();
$hooks = $container->get(ServicesAbstract::HOOKS);

defined('ABSPATH') or die('Direct access not allowed.');

echo '<h2>' . esc_html__('Debug Log', 'post-expirator') . '</h2>';

echo '<p>' . esc_html__(
    'Below is a dump of the debugging table, this should be useful for troubleshooting.',
    'post-expirator'
) . '</p>';

$showSideBar = $hooks->applyFilters(
    HooksAbstract::FILTER_SHOW_PRO_BANNER,
    ! defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')
);

echo '<div class="pp-columns-wrapper' . ($showSideBar ? ' pp-enable-sidebar' : '') . '">';
echo '<div class="pp-column-left">';

// Filter values come from POST to avoid WAF/ModSecurity 403 when params are in URL.
$logCountOptions = [
    500 => '500',
    700 => '700',
    1000 => '1000',
    2500 => '2000',
    5000 => '5000',
    7500 => '7500',
    10000 => '10000',
];
$allowedLogCounts = array_keys($logCountOptions);

$filterNonceAction = 'publishpress_future_debug_log_filter';
$filterSubmitted = isset($_POST['_pp_future_debug_filter_nonce'])
    && wp_verify_nonce(
        sanitize_key($_POST['_pp_future_debug_filter_nonce']),
        $filterNonceAction
    );

if ($filterSubmitted) {
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $postedLogCount = isset($_POST['log_count']) ? (int) $_POST['log_count'] : 500;
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $postedGroupBy = isset($_POST['group_by_request']) ? (int) $_POST['group_by_request'] : 1;
    $currentLogCount = in_array($postedLogCount, $allowedLogCounts, true) ? $postedLogCount : 500;
    $groupByRequest = ($postedGroupBy === 0 || $postedGroupBy === 1) ? $postedGroupBy : 1;
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
    $triggerActivatedOnly = isset($_POST['trigger_activated_only']) ? 1 : 0;
} else {
    $currentLogCount = 500;
    $groupByRequest = 1;
    $triggerActivatedOnly = 0;
}
/**
 * @var LoggerInterface $logger
 */
$logger = Container::getInstance()->get(ServicesAbstract::LOGGER);
$results = $logger->fetchLatest($currentLogCount, (bool)$triggerActivatedOnly);
$totalLogs = $logger->getTotalLogs((bool)$triggerActivatedOnly);
$logSizeInBytes = $logger->getLogSizeInBytes((bool)$triggerActivatedOnly);
$logSizeInBytesTotal = $triggerActivatedOnly ? $logger->getLogSizeInBytes(false) : $logSizeInBytes;
$totalLogsUnfiltered = $triggerActivatedOnly ? $logger->getTotalLogs(false) : $totalLogs;

$uniqueRequestIds = array_unique(array_filter(array_column($results, 'request_id')));
$sessionCount = count($uniqueRequestIds);

echo '<div class="pp-debug-log">';

$viewDebugUrl = admin_url('admin.php?page=publishpress-future-settings&tab=viewdebug');

echo '<div class="pp-debug-log-options">';
echo '<form method="post" action="' . esc_url($viewDebugUrl) . '" id="pp-debug-log-form">';
echo wp_nonce_field($filterNonceAction, '_pp_future_debug_filter_nonce', true, false); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
echo '<div class="pp-debug-log-option">';
echo '<label for="log-count">' . esc_html__('Number of logs to display:', 'post-expirator') . '</label>';
echo '<select id="log-count" name="log_count" onchange="this.form.submit()">';
foreach ($logCountOptions as $value => $label) {
    $selected = $currentLogCount === $value ? ' selected' : '';
    echo '<option value="' . esc_attr((string)$value) . '"' . $selected . '>' . esc_html($label) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
echo '</select>';
echo '</div>';
echo '<div class="pp-debug-log-option">';
echo '<label>' . esc_html__('Display:', 'post-expirator') . '</label>';
echo '<label class="pp-radio-label"><input type="radio" name="group_by_request" value="1" ' . ($groupByRequest === 1 ? 'checked' : '') . ' onchange="this.form.submit()"> ' . esc_html__('Grouped by request', 'post-expirator') . '</label>';
echo '<label class="pp-radio-label"><input type="radio" name="group_by_request" value="0" ' . ($groupByRequest === 0 ? 'checked' : '') . ' onchange="this.form.submit()"> ' . esc_html__('Time sequence', 'post-expirator') . '</label>';
echo '</div>';
echo '<div class="pp-debug-log-option">';
echo '<label class="pp-checkbox-label"><input type="checkbox" name="trigger_activated_only" value="1" ' . ($triggerActivatedOnly ? 'checked' : '') . ' onchange="this.form.submit()"> ' . esc_html__('Show only requests with trigger activated', 'post-expirator') . '</label>';
echo '</div>';
echo '</form>';
echo '<div id="pp-debug-log-autorefresh"></div>';
echo '</div>';

$separator = str_repeat('-', 60);
$previousRequestId = null;

if ($groupByRequest) {
    $results = DebugLogDisplayHelper::sortResultsByRequest($results);
}

echo '<textarea readonly>';
if (empty($results)) {
    if ($totalLogsUnfiltered === 0) {
        echo esc_html__('Debugging table is currently empty.', 'post-expirator');
    } else {
        echo esc_html__('No results match the current filter.', 'post-expirator');
    }
} else {
    foreach ($results as $result) {
        if ($groupByRequest) {
            $requestId = isset($result['request_id']) && $result['request_id'] !== ''
                ? $result['request_id']
                : '(no request id)';
            if ($previousRequestId !== null && $previousRequestId !== $requestId) {
                echo esc_html($separator) . "\n";
            }
            $isNewGroup = $previousRequestId === null || $previousRequestId !== $requestId;
            if ($isNewGroup) {
                echo 'Request ID: ' . esc_html($requestId) . "\n";
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

        printf("%s: %s\n", esc_html($result['timestamp']), esc_html($result['message']));
    }
}
echo '</textarea>';

$totalDisplayedLogs = count($results);

if ($totalLogsUnfiltered === 0) {
    echo '<p id="debug-log-length">' . esc_html__('Debugging table is currently empty.', 'post-expirator') . '</p>';
} elseif ($totalLogs === 0) {
    echo '<p id="debug-log-length">' . esc_html__('No results match the current filter.', 'post-expirator') . '</p>';
} elseif ($totalLogs > $totalDisplayedLogs) {
    $message = $triggerActivatedOnly
        ? sprintf(
            // translators: %1$d: displayed count, %2$d: filtered total, %3$d: sessions, %4$d: total unfiltered, %5$s: filtered log size, %6$s: total log size.
            esc_html__('Showing the latest %1$d of %2$d logs (%3$d sessions). Total in database: %4$d. Log size: %5$s (total: %6$s).', 'post-expirator'),
            esc_html((string) $totalDisplayedLogs),
            esc_html((string) $totalLogs),
            esc_html((string) $sessionCount),
            esc_html((string) $totalLogsUnfiltered),
            esc_html(PostExpirator_Util::formatBytes($logSizeInBytes)),
            esc_html(PostExpirator_Util::formatBytes($logSizeInBytesTotal))
        )
        : sprintf(
            // translators: %1$d: displayed count, %2$d: total count, %3$d: sessions, %4$s: log size.
            esc_html__('Showing the latest %1$d of %2$d logs (%3$d sessions). Log size: %4$s.', 'post-expirator'),
            esc_html((string) $totalDisplayedLogs),
            esc_html((string) $totalLogs),
            esc_html((string) $sessionCount),
            esc_html(PostExpirator_Util::formatBytes($logSizeInBytes))
        );
    echo '<p id="debug-log-length">' . $message . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
} else {
    $message = $triggerActivatedOnly
        ? sprintf(
            // translators: %1$d: filtered logs count, %2$d: sessions, %3$d: total unfiltered, %4$s: filtered log size, %5$s: total log size.
            esc_html__('Showing all %1$d logs (%2$d sessions). Total in database: %3$d. Log size: %4$s (total: %5$s).', 'post-expirator'),
            esc_html((string) $totalLogs),
            esc_html((string) $sessionCount),
            esc_html((string) $totalLogsUnfiltered),
            esc_html(PostExpirator_Util::formatBytes($logSizeInBytes)),
            esc_html(PostExpirator_Util::formatBytes($logSizeInBytesTotal))
        )
        : sprintf(
            // translators: %1$d: total logs count, %2$d: sessions count, %3$s: log size.
            esc_html__('Showing all %1$d logs (%2$d sessions). Log size: %3$s.', 'post-expirator'),
            esc_html((string) $totalLogs),
            esc_html((string) $sessionCount),
            esc_html(PostExpirator_Util::formatBytes($logSizeInBytes))
        );
    echo '<p id="debug-log-length">' . $message . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

echo '<div class="pp-debug-log-actions">';

$nonce = wp_create_nonce('publishpress_future_download_log');
$logActionArgs = [
    'action' => 'publishpress_future_debug_log',
    'nonce' => $nonce,
    'grouped' => $groupByRequest,
    'trigger_activated_only' => $triggerActivatedOnly,
];

echo '<button id="copy-debug-log" class="button">' . esc_html__('Copy Debug Log', 'post-expirator') . '</button>';

echo '<a href="' . esc_url(add_query_arg(array_merge($logActionArgs, ['disposition' => 'attachment']), admin_url('admin.php'))) . '" class="button">'
    . esc_html__('Download', 'post-expirator') . '</a>';

echo '<a href="' . esc_url(add_query_arg($logActionArgs, admin_url('admin.php'))) . '" class="button" target="_blank" rel="noopener noreferrer">'
    . esc_html__('View Full Log in New Tab', 'post-expirator') . '</a>';

echo '</div>';

// Add JavaScript to handle copying
?>
<script>
document.getElementById('copy-debug-log').addEventListener('click', function() {
    const debugLog = document.querySelector('.pp-debug-log textarea');
    debugLog.select();
    document.execCommand('copy');
    alert('<?php echo esc_js(__('Debug log copied to clipboard!', 'post-expirator')); ?>');
});
</script>
<?php

echo '</div>';

// Add JavaScript to auto-scroll textarea to the end (only when it has content)
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const debugLog = document.querySelector('.pp-debug-log textarea');
    if (debugLog && debugLog.scrollHeight > 0) {
        debugLog.scrollTop = debugLog.scrollHeight;
    }
});
</script>
<?php

echo '</div>';

if ($showSideBar) {
    include __DIR__ . '/ad-banner-right-sidebar.php';
}
echo '</div>';
