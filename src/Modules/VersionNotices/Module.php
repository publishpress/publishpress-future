<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\VersionNotices;

use PublishPress\Future\Core\Paths;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Framework\ModuleInterface;
use PublishPress\WordpressVersionNotices\Module\TopNotice\Module as VersionNoticesModule;

\defined('ABSPATH') || exit;

final class Module implements ModuleInterface
{
    /**
     * @var Paths
     */
    private $paths;

    /**
     * @var \PublishPress\Future\Core\HookableInterface
     */
    private $hooks;

    public function __construct(Paths $paths)
    {
        $this->paths = $paths->getBaseDirPath();

        $container = Container::getInstance();
        $this->hooks = $container->get(ServicesAbstract::HOOKS);
    }

    protected function checkLibraryVersion()
    {
        if (
            defined('PP_VERSION_NOTICES_VERSION')
            && version_compare(PP_VERSION_NOTICES_VERSION, '2.1.1', '<')
        ) {
            // Only log this error once in an hour to avoid flooding the log.
            if (! get_transient('pp_future_version_notices_version_error')) {
                set_transient('pp_future_version_notices_version_error', true, HOUR_IN_SECONDS);

                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('PublishPress Future: Library PublishPress Version Notices is not compatible with this version of PublishPress Future. Please update PublishPress plugins to the latest versions.');
            }

            return false;
        }

        return true;
    }

    protected function checkLibraryIsLoaded()
    {
        return class_exists('PublishPress\\WordpressVersionNotices\\Module\\TopNotice\\Module');
    }


    /**
     * @inheritDoc
     */
    public function initialize()
    {
        if (! $this->checkLibraryVersion()) {
            return;
        }

        // This comes after version check, because the class is not available on older versions,
        // generating false positive.
        if (! $this->checkLibraryIsLoaded()) {
            return;
        }

        if (is_admin() && ! defined('PUBLISHPRESS_FUTURE_SKIP_VERSION_NOTICES')) {
            if (! defined('PP_VERSION_NOTICES_LOADED')) {
                $includesPath = $this->paths->getVendorDirPath(
                ) . '/publishpress/wordpress-version-notices/includes.php';

                if (is_file($includesPath) && is_readable($includesPath)) {
                    // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
                    require_once $includesPath;
                }
            }

            if (current_user_can('install_plugins')) {
                $this->hooks->addFilter(
                    VersionNoticesModule::SETTINGS_FILTER,
                    function ($settings) {
                        $settings['publishpress-future'] = [
                            // translators: %1$s and %2$s are placeholders for the link to by the Pro version
                            'message' => __(
                                'You\'re using PublishPress Future Free. The Pro version has more features and support. %1$sUpgrade to Pro%2$s',
                                'post-expirator'
                            ),
                            'link' => 'https://publishpress.com/links/future-banner',
                            'screens' => [
                                [
                                    'base' => 'toplevel_page_publishpress-future',
                                    'id' => 'toplevel_page_publishpress-future',
                                ],
                                [
                                    'base' => 'future_page_publishpress-future-scheduled-actions',
                                    'id' => 'future_page_publishpress-future-scheduled-actions',
                                ],
                                [
                                    'base' => 'edit',
                                    'id' => 'edit-ppfuture_workflow',
                                ],
                                [
                                    'base' => 'admin_page_publishpress-future-scheduled-actions',
                                    'id' => 'admin_page_publishpress-future-scheduled-actions',
                                ],
                                [
                                    'base' => 'future_page_future-backup',
                                    'id' => 'future_page_future-backup',
                                ],
                                [
                                    'base' => 'future_page_publishpress-future-settings',
                                    'id' => 'future_page_publishpress-future-settings',
                                ],
                            ],
                        ];

                        return $settings;
                    }
                );

                $this->hooks->addFilter(
                    'pp_version_notice_menu_link_settings',
                    function ($settings) {
                        $settings['publishpress-future'] = [
                            'parent' => 'publishpress-future',
                            'label' => __('Upgrade to Pro', 'post-expirator'),
                            'link' => 'https://publishpress.com/links/future-menu',
                        ];

                        return $settings;
                    }
                );
            }
        }
    }
}
