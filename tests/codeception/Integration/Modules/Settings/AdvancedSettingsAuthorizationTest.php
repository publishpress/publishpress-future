<?php

/**
 * Integration tests for advanced settings form capability checks.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace Tests\Modules\Settings;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Settings\Controllers\Controller;

class AdvancedSettingsAuthorizationTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $originalPost = [];

    /**
     * @var int
     */
    private $originalUserId = 0;

    public function setUp(): void
    {
        parent::setUp();

        $this->originalPost = $_POST;
        $this->originalUserId = get_current_user_id();
    }

    public function tearDown(): void
    {
        $_POST = $this->originalPost;
        wp_set_current_user($this->originalUserId);

        parent::tearDown();
    }

    public function test_should_not_save_advanced_settings_when_user_cannot_manage_options(): void
    {
        $subscriberId = $this->factory()->user->create(
            [
                'role' => 'subscriber',
            ]
        );
        wp_set_current_user($subscriberId);
        $this->assertFalse(current_user_can('manage_options'));

        $settings = new class {
            /**
             * @var int
             */
            public $writes = 0;

            /**
             * @param bool $value
             */
            public function setExperimentalFeaturesStatus(bool $value): void
            {
                unset($value);
                $this->writes++;
            }

            /**
             * @param bool $value
             */
            public function setStepScheduleCompressedArgsStatus(bool $value): void
            {
                unset($value);
                $this->writes++;
            }

            /**
             * @param bool $value
             */
            public function setScheduledWorkflowStepsCleanupStatus(bool $value): void
            {
                unset($value);
                $this->writes++;
            }

            /**
             * @param int $value
             */
            public function setScheduledWorkflowStepsCleanupRetention(int $value): void
            {
                unset($value);
                $this->writes++;
            }

            /**
             * @param int $value
             */
            public function setPreserveData(int $value): void
            {
                unset($value);
                $this->writes++;
            }
        };

        $_POST = [
            '_postExpiratorMenuAdvanced_nonce' => 'invalid',
            'future-experimental-features' => '1',
            'future-step-schedule-cleanup' => '1',
            'future-step-schedule-cleanup-retention' => '1',
            'expired-preserve-data-deactivating' => '1',
        ];

        $controller = new Controller(
            $this->createMock(HookableInterface::class),
            $settings,
            static function () {
            },
            static function () {
            },
            null,
            static function () {
            },
            $this->createMock(LoggerInterface::class)
        );

        $controller->processFormSubmission();

        $this->assertSame(0, $settings->writes);
    }
}
