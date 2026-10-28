<?php

/**
 * Lifecycle hooks for private Future abilities.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp;

\defined('ABSPATH') || exit;

abstract class HooksAbstract
{
    public const ACTION_REGISTER_CATEGORIES = 'wp_abilities_api_categories_init';
    public const ACTION_REGISTER_ABILITIES = 'wp_abilities_api_init';
    public const ACTION_REGISTER_EXTENSIONS = 'publishpress_future_mcp_register';
}
