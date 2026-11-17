<?php

/**
 * Regression tests for registering Future abilities independently of the init boot.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

namespace Tests\Modules\Mcp;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Mcp\HooksAbstract;

class AbilitiesBootTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /** @since 4.12.0 */
    public function setUp(): void
    {
        parent::setUp();
        if (!function_exists('wp_get_ability')) {
            $this->markTestSkipped('The WordPress Abilities API is unavailable.');
        }
    }

    /**
     * An early registry start runs before Plugin::initialize(), so the hooks must come from the plugin file.
     *
     * @since 4.12.0
     */
    public function testAbilitiesHooksAreAttachedAtPluginFileLoad(): void
    {
        $container = Container::getInstance();

        $this->assertNotFalse(has_action(HooksAbstract::ACTION_REGISTER_CATEGORIES, 'PublishPress\\Future\\registerAbilityCategories'));
        $this->assertNotFalse(has_action(HooksAbstract::ACTION_REGISTER_ABILITIES, 'PublishPress\\Future\\registerAbilities'));
        $this->assertFalse(has_action(HooksAbstract::ACTION_REGISTER_CATEGORIES, [$container->get(ServicesAbstract::MCP_WORDPRESS), 'category']));
        $this->assertFalse(has_action(HooksAbstract::ACTION_REGISTER_ABILITIES, [$container->get(ServicesAbstract::MODULE_MCP), 'register']));
    }

    /** @since 4.12.0 */
    public function testThrowingProviderDoesNotStopLaterAbilitiesCallbacks(): void
    {
        $names = $this->futureAbilityNames();
        $this->assertNotEmpty($names);
        foreach ($names as $name) {
            wp_unregister_ability($name);
        }

        $laterCallbackRan = false;
        $throwForFuture = static function ($args, $name) {
            if (strpos($name, 'publishpress-future/') === 0) {
                throw new \RuntimeException('Provider failure.');
            }
            return $args;
        };
        remove_all_actions(HooksAbstract::ACTION_REGISTER_ABILITIES);
        add_action(HooksAbstract::ACTION_REGISTER_ABILITIES, 'PublishPress\\Future\\registerAbilities', 10, 0);
        add_action(HooksAbstract::ACTION_REGISTER_ABILITIES, static function () use (&$laterCallbackRan) {
            $laterCallbackRan = true;
        }, 20, 0);
        add_filter('wp_register_ability_args', $throwForFuture, 10, 2);

        try {
            do_action(HooksAbstract::ACTION_REGISTER_ABILITIES, \WP_Abilities_Registry::get_instance());
            $this->assertTrue($laterCallbackRan);
            $this->assertSame([], $this->futureAbilityNames());
        } finally {
            remove_filter('wp_register_ability_args', $throwForFuture, 10);
            do_action(HooksAbstract::ACTION_REGISTER_ABILITIES, \WP_Abilities_Registry::get_instance());
        }

        $this->assertSame($names, $this->futureAbilityNames());
    }

    /**
     * @since 4.12.0
     *
     * @return string[]
     */
    private function futureAbilityNames(): array
    {
        $names = [];
        foreach (wp_get_abilities() as $ability) {
            if (strpos($ability->get_name(), 'publishpress-future/') === 0) {
                $names[] = $ability->get_name();
            }
        }
        sort($names);
        return $names;
    }
}
