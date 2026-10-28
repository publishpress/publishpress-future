<?php

use PublishPress\Future\Modules\Settings\HooksAbstract;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;

defined('ABSPATH') || exit;

$container = Container::getInstance();
$hooks = $container->get(ServicesAbstract::HOOKS);
$settingsFacade = $container->get(ServicesAbstract::SETTINGS);

defined('ABSPATH') || exit;

$defaultTab = $settingsFacade->getSettingsDefaultTab();

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$current_tab = empty($_GET['tab']) ? $defaultTab : sanitize_title(wp_unslash($_GET['tab']));

$debugIsEnabled = $hooks->applyFilters(HooksAbstract::FILTER_DEBUG_ENABLED, false);
$baseLink = 'admin.php?page=publishpress-future-settings&tab=';

$tabs = [
    [
        'title' => __('Advanced', 'post-expirator'),
        'slug'  => 'advanced',
        'link' => admin_url($baseLink . 'advanced'),
    ],
    [
        'title' => __('Diagnostics and Tools', 'post-expirator'),
        'slug'  => 'diagnostics',
        'link' => admin_url($baseLink . 'diagnostics'),
    ],
];

if ($debugIsEnabled) {
    $tabs[] = [
        'title' => __('Debug', 'post-expirator'),
        'slug'  => 'viewdebug',
        'link' => admin_url($baseLink . 'viewdebug'),
    ];
}

$tabs = $hooks->applyFilters(HooksAbstract::FILTER_SETTINGS_TABS, $tabs);
?>

<div class="wrap">
    <h1 class="pp-settings-title"><?php
        esc_html_e('Settings', 'post-expirator'); ?></h1>
    <div id="pe-settings-tabs">
        <nav class="nav-tab-wrapper postexpirator-nav-tab-wrapper" id="postexpirator-nav" aria-label="<?php esc_attr_e('Settings sections', 'post-expirator'); ?>">
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
