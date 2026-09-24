<?php

define('ABSPATH', '/tmp');

if (! defined('DB_NAME')) {
    define('DB_NAME', 'wordpress_unit_tests');
}

if (! function_exists('is_admin')) {
    function is_admin()
    {
        return false;
    }
}

if (! function_exists('absint')) {
    function absint($maybeint)
    {
        return (int) $maybeint;
    }
}

if (! function_exists('wp_unslash')) {
    /**
     * @param mixed $value
     * @return mixed
     */
    function wp_unslash($value)
    {
        if (is_string($value)) {
            return stripslashes($value);
        }

        return $value;
    }
}

if (! function_exists('sanitize_key')) {
    /**
     * @param string $key
     * @return string
     */
    function sanitize_key($key)
    {
        $key = strtolower((string) $key);

        return preg_replace('/[^a-z0-9_\-]/', '', $key);
    }
}

use PublishPress\Future\Core\Autoloader;

if (! defined('PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH')) {
    define('PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH', realpath(__DIR__ . '/../../lib/vendor'));
}

$autoloadFilePath = PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH . '/autoload.php';
if (! class_exists('ComposerAutoloaderInitPublishPressFuture')
    && is_file($autoloadFilePath)
    && is_readable($autoloadFilePath)
) {
    require_once $autoloadFilePath;
}

require_once __DIR__ . '/../../lib/vendor/publishpress/psr-container/lib/autoload.php';
require_once __DIR__ . '/../../lib/vendor/publishpress/pimple-pimple/lib/autoload.php';


require_once __DIR__ . '/../../lib/vendor/woocommerce/action-scheduler/action-scheduler.php';

if (! class_exists('PublishPress\Future\Core\Autoloader')) {
    require_once __DIR__ . '/../../src/Core/Autoloader.php';
}
Autoloader::register();
