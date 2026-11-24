<?php

/**
 * Autoloads the classes.
 */

namespace PublishPress\Future\Core;

\defined('ABSPATH') || exit;

class Autoloader
{
    /**
     * Register the autoloader with spl_autoload_register
     *
     * @return void
     */
    public static function register()
    {
        spl_autoload_register([new self(), 'autoload']);
    }

    /**
     * Autoload function that loads classes based on the namespace and class name
     *
     * @param string $class The fully-qualified class name
     *
     * @return void
     */
    public static function autoload($class)
    {
        // base directory for the namespace prefix
        $baseDir = __DIR__ . '/../';

        // namespace prefix
        $prefix = 'PublishPress\\Future\\';

        // does the class use the namespace prefix?
        $len = strlen($prefix);

        if (strncmp($prefix, $class, $len) !== 0) {
            // no, move to the next registered autoloader
            return;
        }

        // get the relative class name
        $relativeClass = substr($class, $len);

        // replace the namespace prefix with the base directory, replace namespace separators with directory separators
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        // if the file exists, require it
        if (file_exists($file)) {
            require $file;
        }
    }
}
