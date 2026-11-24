<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Models;

use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use PublishPress\Psr\Container\ContainerInterface;

\defined('ABSPATH') || exit;

class PostTypesModel
{
    private $hooks;

    /**
     * @var \PublishPress\Future\Modules\Settings\SettingsFacade
     */
    private $settingsModelFactory;

    public function __construct(ContainerInterface $container)
    {
        $this->settingsModelFactory = $container->get(ServicesAbstract::POST_TYPE_SETTINGS_MODEL_FACTORY);
        $this->hooks = $container->get(ServicesAbstract::HOOKS);
    }

    public function getActivatedPostTypes(): array
    {
        $settingsModelFactory = $this->settingsModelFactory;
        $settingsModel = $settingsModelFactory();
        $settings = $settingsModel->getPostTypesSettings();

        $activePostTypes = array_filter($settings, function ($postTypeSettings) {
            return $postTypeSettings['active'];
        });

        return array_keys($activePostTypes);
    }

    /**
     * Returns the post types that are supported.
     *
     * @internal
     *
     * @access private
     */
    public function getPostTypes(): array
    {
        $postTypes = get_post_types(['public' => true]);
        $postTypes = array_merge(
            $postTypes,
            get_post_types(
                [
                    'public' => false,
                    'show_ui' => true,
                ]
            )
        );

        // Allow to customize the list of post types supported by the plugin.
        $postTypes = $this->hooks->applyFilters(HooksAbstract::FILTER_SUPPORTED_POST_TYPES, $postTypes);

        /**
         * @deprecated 3.3.0
         */
        // In case some post types should not be supported.
        $unsetPostTypes = $this->hooks->applyFilters(HooksAbstract::FILTER_UNSET_POST_TYPES_DEPRECATED, ['attachment','wp_navigation']);
        if ($unsetPostTypes) {
            foreach ($unsetPostTypes as $type) {
                unset($postTypes[$type]);
            }
        }

        return $postTypes;
    }
}
