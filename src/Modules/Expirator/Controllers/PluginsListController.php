<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Controllers;

use Closure;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Core\HooksAbstract as CoreHooksAbstract;
use PublishPress\Future\Framework\InitializableInterface;

defined('ABSPATH') or die('Direct access not allowed.');

class PluginsListController implements InitializableInterface
{
    public const SETTINGS_PAGE_CAPABILITY = 'manage_options';

    public const TRANSIENT_REDIRECT_AFTER_ACTIVATION = 'publishpress_future_redirect_after_activation';

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @param HookableInterface $hooksFacade
     */
    public function __construct(HookableInterface $hooksFacade)
    {
        $this->hooks = $hooksFacade;
    }

    public function initialize()
    {
        $this->hooks->addFilter(CoreHooksAbstract::FILTER_PLUGIN_ACTION_LINKS, [$this, 'addPluginActionLinks'], 10, 2);
        $this->hooks->addFilter(CoreHooksAbstract::FILTER_PLUGIN_ROW_META, [$this, 'addPluginRowMetaLinks'], 10, 2);

        $this->redirectAfterActivate();
    }

    public function addPluginActionLinks($links, $file)
    {
        $this_plugin = basename(PUBLISHPRESS_FUTURE_BASE_PATH) . '/post-expirator.php';

        if ($file === $this_plugin) {
            $upgrade_link = ['<a href="https://publishpress.com/links/future-sidebar"
            target="_blank" style="font-weight: bold;">
            ' . __('Upgrade to Pro', 'post-expirator') . '
            </a>'];

            $links = array_merge($upgrade_link, $links);
        }

        return $links;
    }

    public function addPluginRowMetaLinks($links, $file)
    {
        $validPlugins = [];
        $validPlugins[] = basename(PUBLISHPRESS_FUTURE_BASE_PATH) . '/post-expirator.php';

        if (defined('PUBLISHPRESS_FUTURE_PRO_BASE_PATH')) {
            $validPlugins[] = basename(PUBLISHPRESS_FUTURE_PRO_BASE_PATH) . '/publishpress-future-pro.php';
        }

        if (in_array($file, $validPlugins)) {
            $links[] = '<a href="' . admin_url('edit.php?post_type=ppfuture_workflow') . '">' . __('Action Workflows', 'post-expirator') . '</a>';
            $links[] = '<a href="' . admin_url('admin.php?page=publishpress-future-settings') . '">' . __('Settings', 'post-expirator') . '</a>';
        }

        return $links;
    }

    private function redirectAfterActivate()
    {
        if (! current_user_can(self::SETTINGS_PAGE_CAPABILITY)) {
            return;
        }

        if (! get_transient(self::TRANSIENT_REDIRECT_AFTER_ACTIVATION)) {
            return;
        }

        delete_transient(self::TRANSIENT_REDIRECT_AFTER_ACTIVATION);

        wp_safe_redirect(admin_url('edit.php?post_type=ppfuture_workflow'));
        exit;
    }
}
