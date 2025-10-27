<?php

/**
 * Plugin Name: PublishPress Future
 * Plugin URI: http://wordpress.org/extend/plugins/post-expirator/
 * Description: PublishPress Future allows you to schedule automatic changes to posts, pages and other content types.
 * Author: PublishPress
 * Version: 4.9.1
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

global $wp_version;

$min_php_version = '7.4';
$min_wp_version  = '6.7';

// If the PHP or WP version is not compatible, terminate the plugin execution.
$invalid_php_version = version_compare(phpversion(), $min_php_version, '<');
$invalid_wp_version = version_compare($wp_version, $min_wp_version, '<');

if ($invalid_php_version || $invalid_wp_version) {
    return;
}

if (! defined('PUBLISHPRESS_FUTURE_LOADED')) {
    include __DIR__ . '/src/catch-exception.php';

    try {
        define('PUBLISHPRESS_FUTURE_LOADED', true);

        if (! defined('PUBLISHPRESS_FUTURE_BASE_PATH')) {
            /**
             * @deprecated Since 3.1.0. Use the value from service ServicesAbstract::BASE_PATH instead.
             */
            define('PUBLISHPRESS_FUTURE_BASE_PATH', __DIR__);
        }

        if (! defined('PUBLISHPRESS_FUTURE_VERSION')) {
            define('PUBLISHPRESS_FUTURE_VERSION', '4.9.1');
        }

        if (! defined('PUBLISHPRESS_FUTURE_PLUGIN_FILE')) {
            define('PUBLISHPRESS_FUTURE_PLUGIN_FILE', __FILE__);
        }

        if (! defined('PUBLISHPRESS_FUTURE_ASSETS_URL')) {
            define('PUBLISHPRESS_FUTURE_ASSETS_URL', plugins_url('assets', __FILE__));
        }

        if (! defined('PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH')) {
            $vendorPath = __DIR__ . '/lib/vendor';
            if (defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO') && constant('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')) {
                $vendorPath = constant('PUBLISHPRESS_FUTURE_PRO_VENDOR_DIR');
            }

            define('PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH', $vendorPath);
        }

        if (! defined('PUBLISHPRESS_FUTURE_VENDOR_PATH')) {
            /**d
             * @deprecated Since 3.1.0. Use PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH instead.
             */
            define('PUBLISHPRESS_FUTURE_VENDOR_PATH', PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH);
        }

        if (! defined('PUBLISHPRESS_FUTURE_WORKFLOW_EXPERIMENTAL')) {
            define('PUBLISHPRESS_FUTURE_WORKFLOW_EXPERIMENTAL', false);
        }

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
            require_once __DIR__ . '/src/Core/Autoloader.php';
        }
        Autoloader::register();

        function loadDependencies()
        {
            if (defined('PUBLISHPRESS_FUTURE_LOADED_DEPENDENCIES')) {
                return;
            }

            $pluginFile = __FILE__;

            $services = require __DIR__ . '/services.php';
            $container = new Container($services);

            require_once __DIR__ . '/legacy/defines.php';
            require_once __DIR__ . '/legacy/deprecated.php';
            require_once __DIR__ . '/legacy/functions.php';
            require_once __DIR__ . '/legacy/autoload.php';

            define('PUBLISHPRESS_FUTURE_LOADED_DEPENDENCIES', true);
        }

        require_once __DIR__ . '/src/install.php';
        require_once __DIR__ . '/src/uninstall.php';

        HooksFacade::registerActivationHook(__FILE__, __NAMESPACE__ . '\\install');
        HooksFacade::registerDeactivationHook(__FILE__, __NAMESPACE__ . '\\uninstall');

        add_action('init', function () {
            load_plugin_textdomain('post-expirator', false, basename(dirname(__FILE__)) . '/languages/');
        });

        add_action('init', function () {
            $container = null;
            try {
                loadDependencies();

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
}
