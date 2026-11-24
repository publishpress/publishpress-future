<?php

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooks;

defined('ABSPATH') || exit;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps, PSR1.Methods.CamelCapsMethodName.NotCamelCaps

/**
 * Utility functions.
 */
class PostExpirator_CronFacade
{
    /**
     * @return array
     * @deprecated
     */
    public static function get_plugin_cron_events()
    {
        $container = Container::getInstance();
        $cron      = $container->get(ServicesAbstract::CRON);
        return $cron->getScheduledActions(ExpiratorHooks::ACTION_LEGACY_EXPIRE_POST2);
    }

    /**
     * @return bool
     */
    public static function is_cron_enabled()
    {
        return ! defined('DISABLE_WP_CRON') || DISABLE_WP_CRON == false;
    }

    /**
     * @deprecated
     */
    public static function post_has_scheduled_task($post_id)
    {
        $container = Container::getInstance();
        $cron      = $container->get(ServicesAbstract::CRON);
        return $cron->postHasScheduledActions($post_id);
    }
}

// phpcs:enable
