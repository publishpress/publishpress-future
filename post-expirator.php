<?php

/**
 * Plugin Name: PublishPress Future Free
 * Plugin URI: http://wordpress.org/extend/plugins/post-expirator/
 * Description: PublishPress Future allows you to schedule automatic changes to posts, pages and other content types.
 * Author: PublishPress
 * Version: 4.12.0
 * Author URI: http://publishpress.com
 * Text Domain: post-expirator
 * Domain Path: /languages
 * Requires at least: 6.8
 * Requires PHP: 8.2
 *
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2025, PublishPress
 * @license     GPLv2 or later
 */

namespace PublishPress\Future;

use function defined;

defined('ABSPATH') || exit;

// If the plugin is already loaded, terminate the plugin execution.
if (defined('PUBLISHPRESS_FUTURE_LOADED')) {
    return;
}

const MINIMUM_PHP_VERSION = '8.2';
const MINIMUM_WP_VERSION = '6.8';

global $wp_version;

// Exit if PHP or WordPress version requirements are not met.
if (version_compare(PHP_VERSION, MINIMUM_PHP_VERSION, '<')) {
    return;
}

if (version_compare($wp_version, MINIMUM_WP_VERSION, '<')) {
    return;
}

define('PUBLISHPRESS_FUTURE_VERSION', '4.12.0');
define('PUBLISHPRESS_FUTURE_BASE_PATH', __DIR__);
define('PUBLISHPRESS_FUTURE_SRC_PATH', __DIR__ . '/src');
define('PUBLISHPRESS_FUTURE_PLUGIN_FILE', __FILE__);
define('PUBLISHPRESS_FUTURE_ASSETS_URL', plugins_url('assets', __FILE__));
define('PUBLISHPRESS_FUTURE_LOADED', true);
define('PUBLISHPRESS_FUTURE_LANGUAGES_PATH', __DIR__ . '/languages');

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

// Initialize the plugin
require_once __DIR__ . '/src/init.php';
