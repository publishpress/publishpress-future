<?php

/**
 * @author PublishPress
 * @copyright Copyright (c) 2025, Ramble Ventures
 * @license http://www.gnu.org/licenses/gpl-2.0.html GPL v2 or later
 * @package PublishPressFuture
 */

namespace PublishPress\Future;

use Throwable;

use function defined;

defined('ABSPATH') || exit;

if (! function_exists(__NAMESPACE__ . '\\logError')) {

    function logError(string $message, ?Throwable $e = null, bool $addTrace = false): void
    {
        if (! function_exists('error_log')) {
            return;
        }

        if (! defined('WP_DEBUG') || ! WP_DEBUG) {
            return;
        }

        if (! is_null($e) && is_a($e, Throwable::class)) {
            $message .= ' ' . sprintf(
                '- Caught %1$s: %2$s on file %3$s, line %4$d',
                get_class($e),
                $e->getMessage(),
                $e->getFile(),
                $e->getLine()
            );
        }

        if ($addTrace && method_exists($e, 'getTrace')) {
            // Add the backtrace to the log
            $traceItems = array_map(
                function ($item) {
                    if (isset($item['file'])) {
                        return $item['file'] . ':' . $item['line'] . ' ' . $item['function'] . '()';
                    }

                    return '';
                },
                $e->getTrace()
            );

            $message .= ' Trace: ' . implode(' > ', $traceItems);
        }


        // Make the log message binary safe removing any non-printable chars.
        $message = addcslashes($message, "\000..\037\177..\377\\");

        // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
        error_log($message);
    }
}
