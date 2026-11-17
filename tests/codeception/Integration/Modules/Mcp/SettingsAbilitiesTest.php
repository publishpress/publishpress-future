<?php

/**
 * Native settings and permission regression tests for Future abilities.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

namespace Tests\Modules\Mcp;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;

class SettingsAbilitiesTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /** @since 4.12.0 */
    public function setUp(): void
    {
        parent::setUp();
        if (!function_exists('wp_get_ability')) {
            $this->markTestSkipped('The WordPress Abilities API is unavailable.');
        }
        wp_set_current_user(self::factory()->user->create(['role' => 'administrator']));
        Container::getInstance()->get(ServicesAbstract::SETTINGS)->purgeCache();
    }

    /** @since 4.12.0 */
    public function tearDown(): void
    {
        Container::getInstance()->get(ServicesAbstract::SETTINGS)->purgeCache();
        wp_set_current_user(0);
        parent::tearDown();
    }

    /** @since 4.12.0 */
    public function testSettingsPreviewPreservesNativeStorageAndSavePreservesOtherFields(): void
    {
        $reader = wp_get_ability('publishpress-future/get-plugin-settings');
        $writer = wp_get_ability('publishpress-future/update-plugin-settings');
        $before = $reader->execute(['group' => 'admin']);
        $this->assertNotWPError($before);
        $target = $before['settings']->columnStyle === 'simple' ? 'verbose' : 'simple';
        $input = ['group' => 'admin', 'expected_hash' => $before['hash'], 'settings' => ['columnStyle' => $target]];
        $preview = $writer->execute($input + ['dry_run' => true]);
        $this->assertNotWPError($preview);
        $this->assertSame($before['hash'], $reader->execute(['group' => 'admin'])['hash']);
        $saved = $writer->execute($input);
        $this->assertNotWPError($saved);
        $this->assertSame($target, Container::getInstance()->get(ServicesAbstract::SETTINGS)->getColumnStyle());
        $this->assertSame($before['settings']->metaboxTitle, $saved['settings']->metaboxTitle);
        $this->assertWPError($writer->execute($input));
    }

    /** @since 4.12.0 */
    public function testNativeEmailListsAcceptEmptyArraysAndRejectBooleanItems(): void
    {
        $reader = wp_get_ability('publishpress-future/get-plugin-settings');
        $writer = wp_get_ability('publishpress-future/update-plugin-settings');
        $before = $reader->execute(['group' => 'notifications']);
        $saved = $writer->execute(['group' => 'notifications', 'expected_hash' => $before['hash'],
            'settings' => ['emailNotificationAddressesList' => []]]);
        $this->assertNotWPError($saved);
        $this->assertSame([], $saved['settings']->emailNotificationAddressesList);
        $this->assertWPError($writer->execute(['group' => 'notifications', 'expected_hash' => $saved['hash'],
            'settings' => ['emailNotificationAddressesList' => [true]]]));
        $this->assertSame($saved['hash'], $reader->execute(['group' => 'notifications'])['hash']);
    }

    /** @since 4.12.0 */
    public function testAdministratorCannotBypassDeniedManageOptions(): void
    {
        $ability = wp_get_ability('publishpress-future/get-plugin-settings');
        $deny = static function ($caps) {
            $caps['manage_options'] = false;
            return $caps;
        };
        add_filter('user_has_cap', $deny, 100);
        try {
            $this->assertWPError($ability->execute(['group' => 'admin']));
        } finally {
            remove_filter('user_has_cap', $deny, 100);
        }
    }
}
