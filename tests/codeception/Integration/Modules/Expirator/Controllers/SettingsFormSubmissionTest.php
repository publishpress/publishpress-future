<?php

/**
 * Integration tests for Expirator settings form submission scoping.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace Tests\Modules\Expirator\Controllers;

use PublishPress\Future\Core\HooksAbstract;
use PublishPress\Future\Modules\Settings\HooksAbstract as SettingsHooksAbstract;

class SettingsFormSubmissionTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $originalGet = [];

    /**
     * @var array<string, mixed>
     */
    private array $originalPost = [];

    /**
     * @var list<callable>
     */
    private array $filterCallbacks = [];

    /**
     * @var int
     */
    private $originalUserId = 0;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalGet = $_GET;
        $this->originalPost = $_POST;
        $this->originalUserId = get_current_user_id();

        $adminId = $this->factory()->user->create(
            [
                'role' => 'administrator',
            ]
        );
        wp_set_current_user($adminId);
    }

    public function tearDown(): void
    {
        $_GET = $this->originalGet;
        $_POST = $this->originalPost;
        wp_set_current_user($this->originalUserId);

        foreach ($this->filterCallbacks as $callback) {
            remove_filter(SettingsHooksAbstract::FILTER_ALLOWED_TABS, $callback);
        }
        $this->filterCallbacks = [];

        parent::tearDown();
    }

    public function test_should_not_fire_save_tab_hooks_on_foreign_admin_page_with_license_tab(): void
    {
        $licenseSaveCount = 0;
        $defaultsSaveCount = 0;

        add_action(
            SettingsHooksAbstract::ACTION_SAVE_TAB_PREFIX . 'license',
            static function () use (&$licenseSaveCount): void {
                $licenseSaveCount++;
            }
        );

        add_action(
            SettingsHooksAbstract::ACTION_SAVE_TAB_PREFIX . 'defaults',
            static function () use (&$defaultsSaveCount): void {
                $defaultsSaveCount++;
            }
        );

        $this->addLicenseToAllowedTabs();

        $_GET = [
            'page' => 'rudr-simple-media-library-folders',
            'tab' => 'license',
        ];
        $_POST = ['dummy_field' => '1'];

        $this->dispatchAdminInit();

        $this->assertSame(0, $licenseSaveCount);
        $this->assertSame(0, $defaultsSaveCount);
    }

    public function test_should_fire_save_tab_defaults_on_future_actions_page(): void
    {
        $defaultsSaveCount = 0;

        add_action(
            SettingsHooksAbstract::ACTION_SAVE_TAB_PREFIX . 'defaults',
            static function () use (&$defaultsSaveCount): void {
                $defaultsSaveCount++;
            }
        );

        $_GET = [
            'page' => 'publishpress-future',
            'tab' => 'defaults',
        ];
        $_POST = ['dummy_field' => '1'];

        $this->dispatchAdminInit();

        $this->assertSame(1, $defaultsSaveCount);
    }

    public function test_should_fire_save_tab_license_on_future_settings_page(): void
    {
        $licenseSaveCount = 0;

        add_action(
            SettingsHooksAbstract::ACTION_SAVE_TAB_PREFIX . 'license',
            static function () use (&$licenseSaveCount): void {
                $licenseSaveCount++;
            }
        );

        $this->addLicenseToAllowedTabs();

        $_GET = [
            'page' => 'publishpress-future-settings',
            'tab' => 'license',
        ];
        $_POST = ['dummy_field' => '1'];

        $this->dispatchAdminInit();

        $this->assertSame(1, $licenseSaveCount);
    }

    public function test_should_not_fire_save_tab_when_user_cannot_manage_options(): void
    {
        $subscriberId = $this->factory()->user->create(
            [
                'role' => 'subscriber',
            ]
        );
        wp_set_current_user($subscriberId);
        $this->assertFalse(current_user_can('manage_options'));

        $displaySaveCount = 0;

        add_action(
            SettingsHooksAbstract::ACTION_SAVE_TAB_PREFIX . 'display',
            static function () use (&$displaySaveCount): void {
                $displaySaveCount++;
            }
        );

        $_GET = [
            'page' => 'publishpress-future-settings',
            'tab' => 'display',
        ];
        $_POST = ['dummy_field' => '1'];

        $this->dispatchAdminInit();

        $this->assertSame(0, $displaySaveCount);
    }

    private function dispatchAdminInit(): void
    {
        // Core registers privacy policy text on admin_init. These tests are not
        // in wp-admin, so WordPress flags that callback as incorrect usage.
        $this->setExpectedIncorrectUsage('wp_add_privacy_policy_content');

        do_action(HooksAbstract::ACTION_ADMIN_INIT);
    }

    private function addLicenseToAllowedTabs(): void
    {
        $callback = static function (array $tabs): array {
            $tabs[] = 'license';

            return $tabs;
        };

        add_filter(SettingsHooksAbstract::FILTER_ALLOWED_TABS, $callback);
        $this->filterCallbacks[] = $callback;
    }
}
