<?php

/**
 * Unit tests for settings form submission page guard.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace unit\Modules\Expirator\Controllers;

use Codeception\Test\Feature\Stub;
use Codeception\Test\Unit;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Expirator\Controllers\SettingsController;
use PublishPress\Future\Modules\Settings\HooksAbstract as SettingsHooksAbstract;

/**
 * @since 4.11.0
 */
class SettingsControllerTest extends Unit
{
    use Stub;

    /**
     * @var array
     */
    private $originalGet;

    /**
     * @var array
     */
    private $originalPost;

    /**
     * @var string|null
     */
    private $savedAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalGet = $_GET;
        $this->originalPost = $_POST;
        $this->savedAction = null;
    }

    protected function tearDown(): void
    {
        $_GET = $this->originalGet;
        $_POST = $this->originalPost;

        parent::tearDown();
    }

    /**
     * @since 4.11.0
     */
    public function test_should_ignore_post_when_page_is_not_future_settings()
    {
        $_POST = ['unrelated' => '1'];
        $_GET = ['tab' => 'license', 'page' => 'another-plugin'];

        $controller = $this->createController();
        $controller->processFormSubmission();

        $this->assertNull($this->savedAction);
    }

    /**
     * @since 4.11.0
     */
    public function test_should_ignore_post_when_page_query_arg_is_missing()
    {
        $_POST = ['unrelated' => '1'];
        $_GET = ['tab' => 'general'];

        $controller = $this->createController();
        $controller->processFormSubmission();

        $this->assertNull($this->savedAction);
    }

    /**
     * @since 4.11.0
     */
    public function test_should_save_tab_when_request_targets_future_settings()
    {
        $_POST = ['unrelated' => '1'];
        $_GET = [
            'page' => 'publishpress-future-settings',
            'tab' => 'display',
        ];

        $controller = $this->createController();
        $controller->processFormSubmission();

        $this->assertSame(
            SettingsHooksAbstract::ACTION_SAVE_TAB_PREFIX . 'display',
            $this->savedAction
        );
    }

    /**
     * @return SettingsController
     */
    private function createController(): SettingsController
    {
        $savedAction = &$this->savedAction;

        $hooks = $this->makeEmpty(
            HookableInterface::class,
            [
                'applyFilters' => function ($filterName, $valueToBeFiltered) {
                    return $valueToBeFiltered;
                },
                'doAction' => function ($actionName) use (&$savedAction) {
                    $savedAction = $actionName;
                },
            ]
        );

        return new SettingsController(
            $hooks,
            null,
            null,
            null,
            null,
            null,
            $this->makeEmpty(LoggerInterface::class)
        );
    }
}
