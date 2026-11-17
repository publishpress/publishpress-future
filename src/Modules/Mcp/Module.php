<?php

/**
 * Register Future's private Abilities API integration when WordPress starts the registry.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\WordPress\Facade\McpFacade;

\defined('ABSPATH') || exit;

/**
 * The registry can start before Plugin::initialize(), so post-expirator.php attaches these callbacks.
 */
class Module
{
    private HookableInterface $hooks;
    private McpFacade $wordpress;
    private Registry $registry;
    /** @var array<array-key, mixed> */
    private array $providers;

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $providers
     */
    public function __construct(HookableInterface $hooks, McpFacade $wordpress, Registry $registry, array $providers)
    {
        $this->hooks = $hooks;
        $this->wordpress = $wordpress;
        $this->registry = $registry;
        $this->providers = $providers;
    }

    /** @since 4.12.0 */
    public function registerCategory(): void
    {
        if (!$this->wordpress->available()) {
            return;
        }
        $this->wordpress->category();
    }

    /** @since 4.12.0 */
    public function register(): void
    {
        if (!$this->wordpress->available()) {
            return;
        }
        foreach ($this->providers as $provider) {
            $provider->register($this->registry);
        }
        $this->hooks->doAction(HooksAbstract::ACTION_REGISTER_EXTENSIONS, $this->registry);
    }
}
