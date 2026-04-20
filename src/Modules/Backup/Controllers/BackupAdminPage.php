<?php

namespace PublishPress\Future\Modules\Backup\Controllers;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Core\HooksAbstract;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use PublishPress\Future\Modules\Settings\HooksAbstract as SettingsHooksAbstract;

class BackupAdminPage implements InitializableInterface
{
    private HookableInterface $hooks;

    private SettingsFacade $settingsFacade;

    public function __construct(
        HookableInterface $hooks,
        SettingsFacade $settingsFacade
    ) {
        $this->hooks = $hooks;
        $this->settingsFacade = $settingsFacade;
    }

    public function initialize()
    {
        $this->hooks->addAction(
            HooksAbstract::ACTION_ADMIN_ENQUEUE_SCRIPTS,
            [$this, 'enqueueAdminScripts']
        );

        $this->hooks->addFilter(
            SettingsHooksAbstract::FILTER_ALLOWED_SETTINGS_TABS,
            [$this, 'filterAllowedSettingsTabs']
        );

        $this->hooks->addFilter(
            SettingsHooksAbstract::FILTER_SETTINGS_TABS,
            [$this, 'filterSettingsTabs'],
            11
        );

        $this->hooks->addFilter(
            SettingsHooksAbstract::FILTER_SETTINGS_DEFAULT_TAB,
            [$this, 'filterSettingsDefaultTab']
        );

        $this->hooks->addAction(
            SettingsHooksAbstract::ACTION_LOAD_TAB,
            [$this, 'loadTab']
        );
    }

    public function enqueueAdminScripts($screenId)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Only comparing admin page slug; no form action.
        if (! isset($_GET['page']) || sanitize_key(wp_unslash($_GET['page'])) !== 'publishpress-future-settings') {
            return;
        }

        $validTabs = [
            'export',
            'import',
        ];

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No need for nonce verification when just loading scripts
        $currentTab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : '';
        $defaultTab = $this->settingsFacade->getSettingsDefaultTab();

        if (! in_array($currentTab, $validTabs, true) && ! in_array($defaultTab, $validTabs, true)) {
            return;
        }

        wp_enqueue_style("wp-components");
        wp_enqueue_style("wp-edit-post");
        wp_enqueue_style("wp-editor");
        wp_enqueue_style("wp-notices");

        wp_enqueue_script("wp-url");
        wp_enqueue_script("wp-element");
        wp_enqueue_script("wp-components");
        wp_enqueue_script("wp-data");
        wp_enqueue_script("wp-plugins");
        wp_enqueue_script("wp-notices");
        wp_enqueue_script("wp-api-fetch");

        wp_enqueue_script(
            'future_backup_panel',
            Plugin::getScriptUrl('backupPanel'),
            [
                'wp-i18n',
                'wp-components',
                'wp-url',
                'wp-data',
                'wp-element',
                'wp-hooks',
                'wp-api-fetch',
            ],
            PUBLISHPRESS_FUTURE_VERSION,
            true
        );

        wp_localize_script(
            'future_backup_panel',
            'futureBackupPanelData',
            [
                'apiRoot' => esc_url_raw(rest_url()),
            ]
        );

        wp_set_script_translations(
            'future_backup_panel',
            'post-expirator',
            PUBLISHPRESS_FUTURE_BASE_PATH . '/languages'
        );

        wp_enqueue_style(
            'pe-footer',
            Plugin::getAssetUrl('css/footer.css'),
            false,
            PUBLISHPRESS_FUTURE_VERSION
        );

        wp_enqueue_style(
            'pe-settings',
            Plugin::getAssetUrl('css/settings.css'),
            ['pe-footer'],
            PUBLISHPRESS_FUTURE_VERSION
        );

        wp_enqueue_style(
            'pp-wordpress-banners-style',
            Plugin::getAssetUrl('vendor/wordpress-banners/css/style.css'),
            false,
            PUBLISHPRESS_FUTURE_VERSION
        );
    }

    public function filterAllowedSettingsTabs($allowedTabs)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No need for nonce verification when just comparing page
        if (isset($_GET['page']) && $_GET['page'] !== 'publishpress-future-settings') {
            return $allowedTabs;
        }

        $allowedTabs[] = 'export';
        $allowedTabs[] = 'import';

        return $allowedTabs;
    }

    public function filterSettingsTabs($tabs)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No need for nonce verification when just comparing page
        if (isset($_GET['page']) && $_GET['page'] !== 'publishpress-future-settings') {
            return $tabs;
        }

        $baseLink = 'admin.php?page=publishpress-future-settings&tab=';

        $backupTabs = [
            [
                'title' => __('Export', 'post-expirator'),
                'slug'  => 'export',
                'link'  => admin_url($baseLink . 'export'),
            ],
            [
                'title' => __('Import', 'post-expirator'),
                'slug'  => 'import',
                'link'  => admin_url($baseLink . 'import'),
            ]
        ];

        $tabs = array_merge($backupTabs, $tabs);

        return $tabs;
    }

    public function filterSettingsDefaultTab($defaultTab)
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No need for nonce verification when just comparing page
        if (isset($_GET['page']) && $_GET['page'] !== 'publishpress-future-settings') {
            return $defaultTab;
        }

        $defaultTab = 'export';

        return $defaultTab;
    }

    public function loadTab($tab)
    {
        if ($tab !== 'export' && $tab !== 'import') {
            return;
        }

        $basePath = __DIR__ . '/../../../Views/';

        $showSideBar = $this->hooks->applyFilters(
            SettingsHooksAbstract::FILTER_SHOW_PRO_BANNER,
            ! defined('PUBLISHPRESS_FUTURE_LOADED_BY_PRO')
        );

        include $basePath . 'backup-tab.php';
    }
}
