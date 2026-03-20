<?php

/**
 * Plugin Name: PublishPress Future Free
 * Plugin URI: http://wordpress.org/extend/plugins/post-expirator/
 * Description: PublishPress Future allows you to schedule automatic changes to posts, pages and other content types.
 * Author: PublishPress
 * Version: 4.10.0
 * Author URI: http://publishpress.com
 * Text Domain: post-expirator
 * Domain Path: /languages
 * Requires at least: 6.7
 * Requires PHP: 7.4
 *
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2025, PublishPress
 * @license     GPLv2 or later
 */

namespace PublishPress\Future;

use PublishPress\Future\Core\Autoloader;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use Throwable;

defined('ABSPATH') or die('Direct access not allowed.');

// If the plugin is already loaded, terminate the plugin execution.
if (defined('PUBLISHPRESS_FUTURE_LOADED')) {
    return;
}

global $wp_version;

// If the PHP or WP version is not compatible, terminate the plugin execution.
if (version_compare(PHP_VERSION, '7.4', '<') || version_compare($wp_version, '6.7', '<')) {
    return;
}

define('PUBLISHPRESS_FUTURE_VERSION', '4.10.0');
define('PUBLISHPRESS_FUTURE_BASE_PATH', __DIR__);
define('PUBLISHPRESS_FUTURE_SRC_PATH', __DIR__ . '/src');
define('PUBLISHPRESS_FUTURE_PLUGIN_FILE', __FILE__);
define('PUBLISHPRESS_FUTURE_ASSETS_URL', plugins_url('assets', __FILE__));
define('PUBLISHPRESS_FUTURE_LOADED', true);

if (! defined('PUBLISHPRESS_FUTURE_WORKFLOW_EXPERIMENTAL')) {
    define('PUBLISHPRESS_FUTURE_WORKFLOW_EXPERIMENTAL', false);
}

$vendorPath = PUBLISHPRESS_FUTURE_BASE_PATH . '/lib/vendor';
if (defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO') && constant('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')) {
    $vendorPath = constant('PUBLISHPRESS_FUTURE_PRO_VENDOR_DIR');
}
define('PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH', $vendorPath);

/**
 * @deprecated Since 3.1.0. Use PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH instead.
 */
define('PUBLISHPRESS_FUTURE_VENDOR_PATH', PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH);

require_once PUBLISHPRESS_FUTURE_SRC_PATH . '/catch-exception.php';

try {
    $autoloadFilePath = PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH . '/autoload.php';
    if (
        ! class_exists('ComposerAutoloaderInitPublishPressFuture')
        && is_file($autoloadFilePath)
        && is_readable($autoloadFilePath)
    ) {
        require_once $autoloadFilePath;
    }

    require_once PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH . '/woocommerce/action-scheduler/action-scheduler.php';

    if (! class_exists('PublishPress\Future\Core\Autoloader')) {
        require_once PUBLISHPRESS_FUTURE_SRC_PATH . '/Core/Autoloader.php';
    }
    Autoloader::register();

    /**
     * Bootstrap the DI container and legacy autoload. Safe to call multiple times.
     */
    function loadPluginDependencies()
    {
        if (defined('PUBLISHPRESS_FUTURE_LOADED_DEPENDENCIES')) {
            return;
        }

        require_once PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH . '/publishpress/psr-container/lib/autoload.php';
        require_once PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH . '/publishpress/pimple-pimple/lib/autoload.php';

        $services = require PUBLISHPRESS_FUTURE_BASE_PATH . '/services.php';
        $container = new Container($services);

        $pluginFile = PUBLISHPRESS_FUTURE_PLUGIN_FILE;

        require_once PUBLISHPRESS_FUTURE_BASE_PATH . '/legacy/defines.php';
        require_once PUBLISHPRESS_FUTURE_BASE_PATH . '/legacy/deprecated.php';
        require_once PUBLISHPRESS_FUTURE_BASE_PATH . '/legacy/functions.php';
        require_once PUBLISHPRESS_FUTURE_BASE_PATH . '/legacy/autoload.php';

        define('PUBLISHPRESS_FUTURE_LOADED_DEPENDENCIES', true);
    }

    require_once PUBLISHPRESS_FUTURE_SRC_PATH . '/install.php';
    require_once PUBLISHPRESS_FUTURE_SRC_PATH . '/uninstall.php';

    HooksFacade::registerActivationHook(PUBLISHPRESS_FUTURE_PLUGIN_FILE, __NAMESPACE__ . '\\install');
    HooksFacade::registerDeactivationHook(PUBLISHPRESS_FUTURE_PLUGIN_FILE, __NAMESPACE__ . '\\uninstall');

    add_action('init', function () {
        load_plugin_textdomain('post-expirator', false, basename(dirname(PUBLISHPRESS_FUTURE_PLUGIN_FILE)) . '/languages/');
    });

    add_action('init', function () {
        $container = null;
        try {
            loadPluginDependencies();

            $container = Container::getInstance();
            $container->get(ServicesAbstract::PLUGIN)->initialize();
        } catch (Throwable $e) {
            $isLogged = false;

            if (is_object($container)) {
                $logger = $container->get(ServicesAbstract::LOGGER);

                if ($logger instanceof LoggerInterface) {
                    $logger->error('Caught ' . get_class($e) . ': ' . $e->getMessage() . ' on file ' . $e->getFile() . ', line ' . $e->getLine());
                    $isLogged = true;
                }
            }

            if (! $isLogged) {
                logError('PUBLISHPRESS FUTURE', $e);
            }
        }
    }, 10, 0);
} catch (Throwable $e) {
    logError('PUBLISHPRESS FUTURE - Error starting the plugin. File: ' . $e->getFile() . ', Line: ' . $e->getLine(), $e);
}
