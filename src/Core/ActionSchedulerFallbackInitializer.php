<?php

/**
 * Initializes Action Scheduler when its classes load without the procedural API.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace PublishPress\Future\Core;

\defined('ABSPATH') || exit;

/**
 * Loads Action Scheduler functions when another plugin autoloaded the classes first.
 *
 * Action Scheduler 4.1 defines as_* functions only inside ActionScheduler::init().
 * Its version initializer skips init() when the class is already loaded, so the
 * store classes exist while as_get_scheduled_actions() does not.
 *
 * @since 4.11.0
 */
class ActionSchedulerFallbackInitializer
{
    /**
     * Bootstrap filename relative to an Action Scheduler library root.
     *
     * @since 4.11.0
     */
    private const BOOTSTRAP_FILENAME = 'action-scheduler.php';

    /**
     * Parent directories to search above the loaded ActionScheduler class file.
     *
     * The stock layout is classes/abstracts/ActionScheduler.php, three levels
     * below the library root. A small extra margin covers a slightly deeper nest.
     *
     * @since 4.11.0
     */
    private const MAX_BOOTSTRAP_DEPTH = 5;

    /**
     * Ensures the Action Scheduler procedural API is loaded.
     *
     * No-op when as_get_scheduled_actions() already exists. A second
     * ActionScheduler::init() would redeclare those functions and fatal.
     *
     * @return void
     * @since 4.11.0
     */
    public static function ensureInitialized(): void
    {
        if (function_exists('as_get_scheduled_actions')) {
            return;
        }

        // Do not autoload. Only initialize a class that is already in memory.
        if (! class_exists('ActionScheduler', false)) {
            return;
        }

        $classFile = (new \ReflectionClass('ActionScheduler'))->getFileName();

        if (! is_string($classFile) || $classFile === '') {
            return;
        }

        $bootstrapFile = self::resolveBootstrapFile($classFile);

        if ($bootstrapFile === null) {
            return;
        }

        \ActionScheduler::init($bootstrapFile);
    }

    /**
     * Locates a readable action-scheduler.php by walking up from a class file.
     *
     * @param string $classFile Absolute path to the loaded ActionScheduler class file.
     * @return string|null Readable bootstrap path, or null when none is found.
     * @since 4.11.0
     */
    public static function resolveBootstrapFile(string $classFile): ?string
    {
        $directory = dirname($classFile);

        for ($depth = 0; $depth < self::MAX_BOOTSTRAP_DEPTH; $depth++) {
            $candidate = $directory . '/' . self::BOOTSTRAP_FILENAME;

            if (is_readable($candidate)) {
                return $candidate;
            }

            $parent = dirname($directory);

            if ($parent === $directory) {
                break;
            }

            $directory = $parent;
        }

        return null;
    }
}
