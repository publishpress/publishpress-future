<?php

/**
 * Helper for debug log display (sorting, formatting).
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

namespace PublishPress\Future\Modules\Debug;

use PostExpirator_Util;

defined('ABSPATH') or die('Direct access not allowed.');

/**
 * @since 4.10.0
 */
final class DebugLogDisplayHelper
{
    /**
     * Sort log results so entries from the same request stay together.
     * Groups by request_id, ordered by first occurrence of each request.
     *
     * @param array<int, array<string, mixed>> $results Log rows with id and request_id.
     * @return array<int, array<string, mixed>>
     */
    public static function sortResultsByRequest(array $results): array
    {
        if (empty($results)) {
            return $results;
        }

        $requestFirstId = [];
        foreach ($results as $result) {
            $rid = isset($result['request_id']) && $result['request_id'] !== '' ? $result['request_id'] : '(no request id)';
            $id = (int) ($result['id'] ?? 0);
            if (! isset($requestFirstId[$rid]) || $id < $requestFirstId[$rid]) {
                $requestFirstId[$rid] = $id;
            }
        }

        usort($results, function ($a, $b) use ($requestFirstId) {
            $ridA = isset($a['request_id']) && $a['request_id'] !== '' ? $a['request_id'] : '(no request id)';
            $ridB = isset($b['request_id']) && $b['request_id'] !== '' ? $b['request_id'] : '(no request id)';
            $firstA = $requestFirstId[$ridA] ?? 0;
            $firstB = $requestFirstId[$ridB] ?? 0;
            if ($firstA !== $firstB) {
                return $firstA - $firstB;
            }

            return ((int) ($a['id'] ?? 0)) - ((int) ($b['id'] ?? 0));
        });

        return $results;
    }

    /**
     * Builds the footer message describing how many log entries are shown.
     *
     * Mirrors the branching used in src/Views/menu-debug-log.php so the same
     * translated strings can be reused by the REST endpoint that powers the
     * debug log auto-refresh in assets/jsx/settings-debug.jsx.
     *
     * Returns a fully HTML-escaped plain-text string safe for `textContent`
     * and for direct echo in the view.
     *
     * @since 4.10.2
     *
     * @param int  $totalDisplayedLogs   Number of rows actually rendered in the textarea.
     * @param int  $totalLogs            Total rows matching the current filter.
     * @param int  $sessionCount         Number of unique request ids in displayed rows.
     * @param int  $totalLogsUnfiltered  Total rows in the table, ignoring the trigger filter.
     * @param int  $logSizeInBytes       Size in bytes of the filtered log set.
     * @param int  $logSizeInBytesTotal  Size in bytes of the full (unfiltered) log set.
     * @param bool $triggerActivatedOnly Whether the "trigger activated only" filter is on.
     * @return string Escaped plain-text footer message.
     */
    public static function buildFooterMessage(
        int $totalDisplayedLogs,
        int $totalLogs,
        int $sessionCount,
        int $totalLogsUnfiltered,
        int $logSizeInBytes,
        int $logSizeInBytesTotal,
        bool $triggerActivatedOnly
    ): string {
        if ($totalLogsUnfiltered === 0) {
            return esc_html__('Debugging table is currently empty.', 'post-expirator');
        }

        if ($totalLogs === 0) {
            return esc_html__('No results match the current filter.', 'post-expirator');
        }

        if ($totalLogs > $totalDisplayedLogs) {
            if ($triggerActivatedOnly) {
                return sprintf(
                    // translators: %1$d: displayed count, %2$d: filtered total, %3$d: sessions, %4$d: total unfiltered, %5$s: filtered log size, %6$s: total log size.
                    esc_html__('Showing the latest %1$d of %2$d logs (%3$d sessions). Total in database: %4$d. Log size: %5$s (total: %6$s).', 'post-expirator'),
                    esc_html((string) $totalDisplayedLogs),
                    esc_html((string) $totalLogs),
                    esc_html((string) $sessionCount),
                    esc_html((string) $totalLogsUnfiltered),
                    esc_html(PostExpirator_Util::formatBytes($logSizeInBytes)),
                    esc_html(PostExpirator_Util::formatBytes($logSizeInBytesTotal))
                );
            }

            return sprintf(
                // translators: %1$d: displayed count, %2$d: total count, %3$d: sessions, %4$s: log size.
                esc_html__('Showing the latest %1$d of %2$d logs (%3$d sessions). Log size: %4$s.', 'post-expirator'),
                esc_html((string) $totalDisplayedLogs),
                esc_html((string) $totalLogs),
                esc_html((string) $sessionCount),
                esc_html(PostExpirator_Util::formatBytes($logSizeInBytes))
            );
        }

        if ($triggerActivatedOnly) {
            return sprintf(
                // translators: %1$d: filtered logs count, %2$d: sessions, %3$d: total unfiltered, %4$s: filtered log size, %5$s: total log size.
                esc_html__('Showing all %1$d logs (%2$d sessions). Total in database: %3$d. Log size: %4$s (total: %5$s).', 'post-expirator'),
                esc_html((string) $totalLogs),
                esc_html((string) $sessionCount),
                esc_html((string) $totalLogsUnfiltered),
                esc_html(PostExpirator_Util::formatBytes($logSizeInBytes)),
                esc_html(PostExpirator_Util::formatBytes($logSizeInBytesTotal))
            );
        }

        return sprintf(
            // translators: %1$d: total logs count, %2$d: sessions count, %3$s: log size.
            esc_html__('Showing all %1$d logs (%2$d sessions). Log size: %3$s.', 'post-expirator'),
            esc_html((string) $totalLogs),
            esc_html((string) $sessionCount),
            esc_html(PostExpirator_Util::formatBytes($logSizeInBytes))
        );
    }
}
