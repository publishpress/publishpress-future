<?php

/**
 * PublishPress Future Free
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace PublishPress\Future;

use PublishPress\BundledTranslations\BundledTranslations;
use PublishPress\Future\Core\ActionSchedulerFallbackInitializer;
use PublishPress\Future\Core\Autoloader;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ContainerInterface;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use Throwable;

\defined('ABSPATH') || exit;

require_once __DIR__ . '/catch-exception.php';

try {
    $autoloadFilePath = PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH . '/autoload.php';
    if (
        ! class_exists('ComposerAutoloaderInitPublishPressFuture')
        && is_file($autoloadFilePath)
        && is_readable($autoloadFilePath)
    ) {
        require_once $autoloadFilePath;
    }

    // Register versioned library loaders before their plugins_loaded callbacks run.
    require_once PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH . '/publishpress/psr-container/lib/include.php';
    require_once PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH . '/publishpress/pimple-pimple/lib/include.php';

    require_once PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH . '/woocommerce/action-scheduler/action-scheduler.php';
    require_once PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH . '/publishpress/bundled-translations/core/include.php';

    if (! class_exists('PublishPress\Future\Core\Autoloader')) {
        require_once __DIR__ . '/Core/Autoloader.php';
    }
    Autoloader::register();

    // Register every_minute before WP Cron can reschedule AS queue runner.
    ActionSchedulerFallbackInitializer::registerCronSchedules();

    /**
     * Ensure Action Scheduler is initialized.
     */
    add_action(
        'plugins_loaded',
        [ActionSchedulerFallbackInitializer::class, 'ensureInitialized'],
        2
    );

    if (did_action('plugins_loaded')) {
        ActionSchedulerFallbackInitializer::ensureInitialized();
    }

    /**
     * Bootstrap the DI container and legacy autoload. Safe to call multiple times.
     */
    function loadPluginDependencies()
    {
        if (defined('PUBLISHPRESS_FUTURE_LOADED_DEPENDENCIES')) {
            return;
        }

        require_once PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH . '/publishpress/psr-container/lib/include.php';
        require_once PUBLISHPRESS_FUTURE_LIB_VENDOR_PATH . '/publishpress/pimple-pimple/lib/include.php';

        $services = require PUBLISHPRESS_FUTURE_BASE_PATH . '/services.php';
        $container = new Container($services);

        $pluginFile = PUBLISHPRESS_FUTURE_PLUGIN_FILE;

        require_once PUBLISHPRESS_FUTURE_BASE_PATH . '/legacy/defines.php';
        require_once PUBLISHPRESS_FUTURE_BASE_PATH . '/legacy/deprecated.php';
        require_once PUBLISHPRESS_FUTURE_BASE_PATH . '/legacy/functions.php';
        require_once PUBLISHPRESS_FUTURE_BASE_PATH . '/legacy/autoload.php';

        define('PUBLISHPRESS_FUTURE_LOADED_DEPENDENCIES', true);
    }

    /**
     * Log a caught error through the plugin logger, falling back to the PHP error log.
     *
     * @since 4.12.0
     */
    function logCaughtError(?ContainerInterface $container, Throwable $e): void
    {
        if (is_object($container)) {
            try {
                $logger = $container->get(ServicesAbstract::LOGGER);

                if ($logger instanceof LoggerInterface) {
                    $logger->error('Caught ' . get_class($e) . ': ' . $e->getMessage() . ' on file ' . $e->getFile() . ', line ' . $e->getLine());
                    return;
                }
            } catch (Throwable $loggerError) {
                logError('PUBLISHPRESS FUTURE', $loggerError);
            }
        }

        logError('PUBLISHPRESS FUTURE', $e);
    }

    /**
     * WordPress starts the abilities registry on first access, which can happen before the init boot.
     *
     * @since 4.12.0
     */
    function registerAbilityCategories(): void
    {
        $container = null;
        try {
            loadPluginDependencies();

            $container = Container::getInstance();
            $container->get(ServicesAbstract::MODULE_MCP)->registerCategory();
        } catch (Throwable $e) {
            logCaughtError($container, $e);
        }
    }

    /**
     * @since 4.12.0
     */
    function registerAbilities(): void
    {
        $container = null;
        try {
            loadPluginDependencies();

            $container = Container::getInstance();
            $container->get(ServicesAbstract::MODULE_MCP)->register();
        } catch (Throwable $e) {
            logCaughtError($container, $e);
        }
    }

    HooksFacade::registerActivationHook(PUBLISHPRESS_FUTURE_PLUGIN_FILE, function () {
        if (class_exists(Plugin::class)) {
            Plugin::onActivate();
        }
    });
    HooksFacade::registerDeactivationHook(PUBLISHPRESS_FUTURE_PLUGIN_FILE, function () {
        loadPluginDependencies();

        if (class_exists(Plugin::class)) {
            Plugin::onDeactivate();
        }
    });

    add_action('init', function () {
        load_plugin_textdomain('post-expirator', false, PUBLISHPRESS_FUTURE_LANGUAGES_PATH);
    });

    add_action('plugins_loaded', function () {
        if (! class_exists('PublishPress\\BundledTranslations\\BundledTranslations')) {
            return;
        }

        $bundledTranslations = new BundledTranslations(
            'post-expirator',
            PUBLISHPRESS_FUTURE_LANGUAGES_PATH,
            PUBLISHPRESS_FUTURE_PLUGIN_FILE
        );
        $bundledTranslations->init();
    });

    add_filter('publishpress_bundled_translations_hub_notice_enabled', function ($enabled, $domain) {
        if ($domain === 'post-expirator') {
            return true;
        }

        return $enabled;
    }, 10, 2);

    add_filter('publishpress_bundled_translations_hub_notice_screens', function ($screens, $domain) {
        if ($domain === 'post-expirator') {
            if (! is_array($screens)) {
                $screens = [];
            }

            return array_merge($screens, [
                'toplevel_page_publishpress-future',
                'future_page_publishpress-future-settings',
                'edit-ppfuture_workflow',
                'admin_page_future_workflow_editor',
                'future_page_publishpress-future-scheduled-actions',
                'admin_page_publishpress-future-scheduled-actions',
            ]);
        }

        return $screens;
    }, 10, 2);

    add_action('init', function () {
        $container = null;
        try {
            loadPluginDependencies();

            $container = Container::getInstance();
            $container->get(ServicesAbstract::PLUGIN)->initialize();
        } catch (Throwable $e) {
            logCaughtError($container, $e);
        }
    }, 10, 0);

    add_action('wp_abilities_api_categories_init', __NAMESPACE__ . '\\registerAbilityCategories', 10, 0);
    add_action('wp_abilities_api_init', __NAMESPACE__ . '\\registerAbilities', 10, 0);
} catch (Throwable $e) {
    logError('PUBLISHPRESS FUTURE - Error starting the plugin. File: ' . $e->getFile() . ', Line: ' . $e->getLine(), $e);
}
