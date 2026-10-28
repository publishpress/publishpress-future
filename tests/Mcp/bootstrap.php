<?php

/**
 * Check native dependency loading before plugins_loaded and during late activation.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

if (PHP_SAPI !== 'cli' || !isset($argv[1], $argv[2], $argv[3]) || !in_array($argv[3], ['normal', 'late'], true)) {
    exit("Usage: php bootstrap.php /isolated/wp-load.php /plugin/entrypoint.php normal|late [skip-psr-composer]\n");
}
if (($argv[4] ?? '') === 'skip-psr-composer') {
    // Simulate a bundle whose Composer loader does not initialize the versioned PSR library.
    // This process-local flag avoids modifying generated vendor files in the regression test.
    $files = require dirname($argv[2]) . '/lib/vendor/composer/autoload_files.php';
    foreach ($files as $identifier => $file) {
        if (substr(str_replace('\\', '/', $file), -strlen('/publishpress/psr-container/lib/include.php')) === '/publishpress/psr-container/lib/include.php') {
            $GLOBALS['__composer_autoload_files'][$identifier] = true;
        }
    }
}
require $argv[1];
if ($GLOBALS['wpdb']->prefix !== 'mcp_smoke_') {
    throw new RuntimeException('Use an isolated mcp_smoke_ database.');
}
if ($argv[3] === 'late') {
    if (interface_exists('PublishPress\Psr\Container\ContainerInterface', false)) {
        throw new RuntimeException('Late activation must start without an already initialized PSR container.');
    }
    require $argv[2];
}
if ($argv[3] === 'late') {
    do_action('activate_' . plugin_basename($argv[2]));
    if (get_option('expirationdateDefaultDate') === false) {
        throw new RuntimeException('Late activation must initialize native default settings without requiring the container.');
    }
    echo "PASS: late plugin activation retains the native container-independent installer.\n";
    return;
}
if (!interface_exists('PublishPress\Psr\Container\ContainerInterface')) {
    throw new RuntimeException('The native PSR container interface must be initialized before the Future container.');
}
if (!class_exists('PublishPress\Pimple\Container')) {
    throw new RuntimeException('The native Pimple container must be initialized before the Future container.');
}
\PublishPress\Future\loadPluginDependencies();
$container = \PublishPress\Future\Core\DI\Container::getInstance();
\PublishPress\Future\loadPluginDependencies();
if ($container !== \PublishPress\Future\Core\DI\Container::getInstance()) {
    throw new RuntimeException('Dependency initialization must be idempotent.');
}
if ($argv[3] === 'normal' && getenv('FUTURE_MCP_MODE') === 'pro' && !defined('PUBLISHPRESS_FUTURE_PRO_LOADED')) {
    throw new RuntimeException('The actual Pro plugin must finish normal initialization.');
}
echo 'PASS: native PSR/Pimple dependencies, idempotent container and ' . $argv[3] . " plugin bootstrap.\n";
