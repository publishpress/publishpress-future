<?php

/**
 * Check missing-API initialization and lazy Pro/shared-Free compatibility without WordPress.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

if (PHP_SAPI !== 'cli' || function_exists('wp_register_ability')) {
    exit("Run this check independently of WordPress.\n");
}
define('ABSPATH', __DIR__ . '/');
$root = dirname(__DIR__, 2);
require $root . '/src/Core/HookableInterface.php';
require $root . '/src/Framework/InitializableInterface.php';
require $root . '/src/Framework/WordPress/Facade/HooksFacade.php';
require $root . '/src/Framework/WordPress/Facade/McpFacade.php';
require $root . '/src/Modules/Mcp/Registry.php';
require $root . '/src/Modules/Mcp/Module.php';
$wordpress = new \PublishPress\Future\Framework\WordPress\Facade\McpFacade();
$registry = new \PublishPress\Future\Modules\Mcp\Registry($wordpress);
$hooks = new \PublishPress\Future\Framework\WordPress\Facade\HooksFacade();
$module = new \PublishPress\Future\Modules\Mcp\Module($hooks, $wordpress, $registry, []);
$module->initialize();
if ($wordpress->available()) {
    throw new RuntimeException('Missing API must not be reported available.');
}
if (isset($argv[1])) {
    require $argv[1] . '/src/classes/Modules/Mcp/Module.php';
    $captured = [];
    function add_action($name, $callback, $priority = 10, $accepted = 1)
    {
        $GLOBALS['captured'][$name] = $callback;
        return true;
    }
    $pro = new \PublishPress\FuturePro\Modules\Mcp\Module($hooks, static function () {
        throw new RuntimeException('An older Free package must not resolve new shared providers.');
    });
    $pro->initialize();
    if (!isset($captured['publishpress_future_mcp_register'])) {
        throw new RuntimeException('Pro extension hook was not registered.');
    }
}
echo "PASS: missing Abilities API and lazy Pro extension initialization.\n";
