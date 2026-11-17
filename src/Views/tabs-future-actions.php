<?php

use PublishPress\Future\Modules\Settings\HooksAbstract;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;

defined('ABSPATH') || exit;

$container = Container::getInstance();
$hooks = $container->get(ServicesAbstract::HOOKS);

defined('ABSPATH') || exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_tab = empty($_GET['tab']) ? 'defaults' : sanitize_title(wp_unslash($_GET['tab']));

$debugIsEnabled = $hooks->applyFilters(HooksAbstract::FILTER_DEBUG_ENABLED, false);
$baseLink = 'admin.php?page=publishpress-future&tab=';

$tabs = [
    [
        'title' => __('Post Types', 'post-expirator'),
        'slug'  => 'defaults',
        'link' => admin_url($baseLink . 'defaults'),
    ],
    [
        'title' => __('General', 'post-expirator'),
        'slug'  => 'general',
        'link' => admin_url($baseLink . 'general'),
    ],
    [
        'title' => __('Notifications', 'post-expirator'),
        'slug'  => 'notifications',
        'link' => admin_url($baseLink . 'notifications'),
    ],
    [
        'title' => __('Display', 'post-expirator'),
        'slug'  => 'display',
        'link' => admin_url($baseLink . 'display'),
    ],
    [
        'title' => __('Admin', 'post-expirator'),
        'slug'  => 'admin',
        'link' => admin_url($baseLink . 'admin'),
    ],
];

$tabs = $hooks->applyFilters(HooksAbstract::FILTER_FUTURE_ACTIONS_TABS, $tabs);
?>

<div class="wrap">
    <h1 class="pp-settings-title"><?php
        esc_html_e('Future Actions', 'post-expirator'); ?></h1>
    <div id="pe-settings-tabs">
        <nav class="nav-tab-wrapper postexpirator-nav-tab-wrapper" id="postexpirator-nav" aria-label="<?php esc_attr_e('Future Actions sections', 'post-expirator'); ?>">
            <?php foreach ($tabs as $tab) : ?>
                <a href="<?php echo esc_url($tab['link']); ?>"
                   class="pe-tab nav-tab <?php
                    echo($current_tab === $tab['slug'] ? 'nav-tab-active' : ''); ?>"
                    <?php echo $current_tab === $tab['slug'] ? 'aria-current="page"' : ''; ?>
                >
                    <?php echo esc_html($tab['title']); ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $html;
        ?>

    </div>
</div>
