<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Settings;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\ModuleInterface;
use PublishPress\Future\Modules\Settings\Controllers\Controller;

\defined('ABSPATH') || exit;

final class Module implements ModuleInterface
{
    /**
     * @var Controller
     */
    private $controller;

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var SettingsFacade
     */
    private $settings;

    /**
     * @var \Closure
     */
    private $settingsPostTypesModelFactory;

    /**
     * @var \Closure
     */
    private $taxonomiesModelFactory;

    /**
     * @var \PublishPress\Future\Modules\Expirator\Models\ExpirationActionsModel
     */
    private $actionsModel;

    /**
     * @var \Closure
     */
    private $migrationsFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param HookableInterface $hooks
     * @param SettingsFacade $settings
     * @param \Closure $settingsPostTypesModelFactory
     * @param \Closure $taxonomiesModelFactory
     * @param \PublishPress\Future\Modules\Expirator\Models\ExpirationActionsModel $actionsModel
     * @param \Closure $migrationsFactoy
     * @param LoggerInterface $logger
     */
    public function __construct(
        HookableInterface $hooks,
        $settings,
        $settingsPostTypesModelFactory,
        $taxonomiesModelFactory,
        $actionsModel,
        $migrationsFactoy,
        LoggerInterface $logger
    ) {
        $this->hooks = $hooks;
        $this->settings = $settings;
        $this->settingsPostTypesModelFactory = $settingsPostTypesModelFactory;
        $this->taxonomiesModelFactory = $taxonomiesModelFactory;
        $this->actionsModel = $actionsModel;
        $this->migrationsFactory = $migrationsFactoy;
        $this->logger = $logger;

        $this->controller = $this->getController();
    }

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $this->controller->initialize();
    }

    private function getController()
    {
        return new Controller(
            $this->hooks,
            $this->settings,
            $this->settingsPostTypesModelFactory,
            $this->taxonomiesModelFactory,
            $this->actionsModel,
            $this->migrationsFactory,
            $this->logger
        );
    }
}
