<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\WooCommerce;

use PublishPress\Future\Framework\ModuleInterface;
use PublishPress\Future\Core\HookableInterface;

defined('ABSPATH') or die('Direct access not allowed.');

final class Module implements ModuleInterface
{
    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var string
     */
    private $baseUrl;

    /**
     * @var string
     */
    private $pluginVersion;


    public function __construct(HookableInterface $hooks, $baseUrl, $pluginVersion)
    {
        $this->hooks = $hooks;
        $this->baseUrl = $baseUrl;
        $this->pluginVersion = $pluginVersion;
    }

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $this->hooks->addAction('admin_enqueue_scripts', [$this, 'enqueueStyle']);
    }

    public function enqueueStyle()
    {
        $currentScreen = get_current_screen();

        if (! is_admin()) {
            return;
        }

        if ($currentScreen->base !== 'edit') {
            return;
        }

        if ($currentScreen->post_type !== 'product') {
            return;
        }

        wp_enqueue_style(
            'publishpress-future-woocommerce',
            $this->baseUrl . 'assets/css/woocommerce.css',
            array(),
            $this->pluginVersion,
            false
        );
    }
}
