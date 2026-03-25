<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Debug;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\ModuleInterface;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Debug\Controllers\Controller;
use PublishPress\Future\Modules\Debug\Controllers\RestApiController;

defined('ABSPATH') or die('Direct access not allowed.');

final class Module implements ModuleInterface
{
    /**
     * @var HooksFacade
     */
    private $hooks;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Controller
     */
    private $controller;

    /**
     * @var RestApiController
     */
    private $restApiController;

    /**
     * @param HooksFacade $hooks
     * @param LoggerInterface $logger
     */
    public function __construct(HooksFacade $hooks, LoggerInterface $logger)
    {
        $this->hooks = $hooks;
        $this->logger = $logger;

        $this->controller = new Controller($this->hooks, $this->logger);
        $this->restApiController = new RestApiController($this->hooks, $this->logger);
    }

    /**
     * @inheritDoc
     */
    public function initialize()
    {
        $this->controller->initialize();
        $this->restApiController->initialize();
    }
}
