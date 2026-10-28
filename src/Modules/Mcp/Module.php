<?php

/**
 * Initialize Future's private Abilities API integration through its module lifecycle.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Framework\WordPress\Facade\McpFacade;

\defined('ABSPATH') || exit;

class Module implements InitializableInterface
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

    /**
     * @since 4.12.0
     *
     * @return mixed
     */
    public function initialize()
    {
        if (!$this->wordpress->available()) {
            return;
        }
        $this->hooks->addAction(HooksAbstract::ACTION_REGISTER_CATEGORIES, [$this->wordpress, 'category']);
        $this->hooks->addAction(HooksAbstract::ACTION_REGISTER_ABILITIES, [$this, 'register']);
    }

    /** @since 4.12.0 */
    public function register(): void
    {
        foreach ($this->providers as $provider) {
            $provider->register($this->registry);
        }
        $this->hooks->doAction(HooksAbstract::ACTION_REGISTER_EXTENSIONS, $this->registry);
    }
}
