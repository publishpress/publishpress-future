<?php

use PublishPress\Future\Core\DI\ServicesAbstract;

defined('ABSPATH') || exit;

$defaultData = $container->get(ServicesAbstract::DEFAULT_DATA);

if (! defined('POSTEXPIRATOR_VERSION')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_VERSION', $container->get(ServicesAbstract::PLUGIN_VERSION));
}

if (! defined('POSTEXPIRATOR_DATEFORMAT')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_DATEFORMAT', $defaultData[ServicesAbstract::DEFAULT_DATE_FORMAT]);
}

if (! defined('POSTEXPIRATOR_TIMEFORMAT')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_TIMEFORMAT', $defaultData[ServicesAbstract::DEFAULT_TIME_FORMAT]);
}

if (! defined('POSTEXPIRATOR_FOOTERCONTENTS')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_FOOTERCONTENTS', $defaultData[ServicesAbstract::DEFAULT_FOOTER_CONTENT]);
}

if (! defined('POSTEXPIRATOR_FOOTERSTYLE')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_FOOTERSTYLE', $defaultData[ServicesAbstract::DEFAULT_FOOTER_STYLE]);
}

if (! defined('POSTEXPIRATOR_FOOTERDISPLAY')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_FOOTERDISPLAY', $defaultData[ServicesAbstract::DEFAULT_FOOTER_DISPLAY]);
}

if (! defined('POSTEXPIRATOR_EMAILNOTIFICATION')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_EMAILNOTIFICATION', $defaultData[ServicesAbstract::DEFAULT_EMAIL_NOTIFICATION]);
}

if (! defined('POSTEXPIRATOR_EMAILNOTIFICATIONADMINS')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_EMAILNOTIFICATIONADMINS', $defaultData[ServicesAbstract::DEFAULT_EMAIL_NOTIFICATION_ADMINS]);
}

if (! defined('POSTEXPIRATOR_DEBUGDEFAULT')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_DEBUGDEFAULT', $defaultData[ServicesAbstract::DEFAULT_DEBUG]);
}

if (! defined('POSTEXPIRATOR_EXPIREDEFAULT')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_EXPIREDEFAULT', $defaultData[ServicesAbstract::DEFAULT_EXPIRATION_DATE]);
}

if (! defined('POSTEXPIRATOR_SLUG')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_SLUG', $container->get(ServicesAbstract::PLUGIN_SLUG));
}

if (! defined('POSTEXPIRATOR_BASEDIR')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_BASEDIR', $container->get(ServicesAbstract::BASE_PATH));
}

if (! defined('POSTEXPIRATOR_BASENAME')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_BASENAME', basename($pluginFile));
}

if (! defined('POSTEXPIRATOR_BASEURL')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_BASEURL', $container->get(ServicesAbstract::BASE_URL));
}

if (! defined('POSTEXPIRATOR_LOADED')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_LOADED', true);
}

if (! defined('POSTEXPIRATOR_LEGACYDIR')) {
    /**
     * @deprecated 2.8.0
     */
    define('POSTEXPIRATOR_LEGACYDIR', $container->get(ServicesAbstract::BASE_PATH) . '/legacy');
}
