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
}
