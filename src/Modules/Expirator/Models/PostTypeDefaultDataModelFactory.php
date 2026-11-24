<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Models;

use PublishPress\Future\Framework\System\DateTimeHandlerInterface;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Framework\WordPress\Facade\OptionsFacade;
use PublishPress\Future\Modules\Settings\SettingsFacade;

\defined('ABSPATH') || exit;

class PostTypeDefaultDataModelFactory
{
    /**
     * @var \PublishPress\Future\Modules\Settings\SettingsFacade
     */
    private $settings;

    /**
     * @var \PublishPress\Future\Framework\WordPress\Facade\OptionsFacade
     */
    private $options;

    /**
     * @var HooksFacade
     */
    private $hooks;

    /**
     * @var DateTimeHandlerInterface
     */
    private $dateTimeHandler;

    /**
     * @param \PublishPress\Future\Modules\Settings\SettingsFacade $settings
     * @param \PublishPress\Future\Framework\WordPress\Facade\OptionsFacade $options
     */
    public function __construct(
        SettingsFacade $settings,
        OptionsFacade $options,
        HooksFacade $hooks,
        DateTimeHandlerInterface $dateTimeHandler
    ) {
        $this->settings = $settings;
        $this->options = $options;
        $this->hooks = $hooks;
        $this->dateTimeHandler = $dateTimeHandler;
    }

    public function create(string $postType)
    {
        return new PostTypeDefaultDataModel(
            $this->settings,
            $this->options,
            $postType,
            $this->hooks,
            $this->dateTimeHandler
        );
    }
}
