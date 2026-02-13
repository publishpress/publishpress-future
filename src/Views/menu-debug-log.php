<?php

use PublishPress\Future\Modules\Settings\HooksAbstract;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Framework\Logger\LoggerInterface;

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

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$currentLogCount = isset($_GET['log_count']) ? (int)$_GET['log_count'] : 500;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$groupByRequest = isset($_GET['group_by_request']) ? (int)$_GET['group_by_request'] : 1;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$triggerActivatedOnly = isset($_GET['trigger_activated_only']) ? (int)$_GET['trigger_activated_only'] : 0;
/**
 * @var LoggerInterface $logger
 */
$logger = Container::getInstance()->get(ServicesAbstract::LOGGER);
$results = $logger->fetchLatest($currentLogCount, (bool)$triggerActivatedOnly);
$totalLogs = $logger->getTotalLogs();
$logSizeInBytes = $logger->getLogSizeInBytes();

echo '<div class="pp-debug-log">';

$logCountOptions = [
    500 => '500',
    700 => '700',
    1000 => '1000',
    2500 => '2000',
    5000 => '5000',
    7500 => '7500',
    10000 => '10000'
];

echo '<div class="pp-debug-log-options">';
echo '<form method="get" id="pp-debug-log-form">';
echo '<input type="hidden" name="page" value="publishpress-future-settings">';
echo '<input type="hidden" name="tab" value="viewdebug">';
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
echo '</div>';

$separator = str_repeat('-', 60);
$previousRequestId = null;

echo '<textarea readonly>';
if (empty($results)) {
    if ($totalLogs === 0) {
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
        $previousRequestId = $requestId;
        $requestIdPrefix = $requestId !== '(no request id)'
            ? '[' . esc_html($requestId) . '] '
            : '';
    } else {
        $requestIdPrefix = isset($result['request_id']) && $result['request_id'] !== ''
            ? '[' . esc_html($result['request_id']) . '] '
            : '';
    }
    printf("%s%s: %s\n", $requestIdPrefix, esc_html($result['timestamp']), esc_html($result['message']));
}
}
echo '</textarea>';

$totalDisplayedLogs = count($results);

if ($totalLogs === 0) {
    echo '<p id="debug-log-length">' . esc_html__('Debugging table is currently empty.', 'post-expirator') . '</p>';
} elseif ($totalDisplayedLogs === 0) {
    echo '<p id="debug-log-length">' . esc_html__('No results match the current filter.', 'post-expirator') . '</p>';
} elseif ($totalLogs > $totalDisplayedLogs) {
    echo '<p id="debug-log-length">' . sprintf(
        // translators: %1$d: displayed count, %2$d: total count, %3$s: log size.
        esc_html__('Showing the latest %1$d of %2$d results. The approximate size of the log is %3$s.', 'post-expirator'),
        $totalDisplayedLogs,
        $totalLogs,
        esc_html(PostExpirator_Util::formatBytes($logSizeInBytes))
    ) . '</p>';
} else {
    echo '<p id="debug-log-length">' . sprintf(
        // translators: %1$d: total count, %2$s: log size.
        esc_html__('Showing all %1$d results. The approximate size of the log is %2$s.', 'post-expirator'),
        $totalLogs,
        esc_html(PostExpirator_Util::formatBytes($logSizeInBytes))
    ) . '</p>';
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
