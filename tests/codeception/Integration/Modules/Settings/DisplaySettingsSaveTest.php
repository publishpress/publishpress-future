<?php

/**
 * Integration tests for Display settings save handling.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace Tests\Modules\Settings;

use PostExpirator_Display;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;

class DisplaySettingsSaveTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $originalPost = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->originalPost = $_POST;
    }

    public function tearDown(): void
    {
        $_POST = $this->originalPost;

        parent::tearDown();
    }

    public function testMenuDisplaySaveUnslashesDateFormatBackslashes(): void
    {
        $expectedDateFormat = 'l \d. j F Y';
        $expectedTimeFormat = 'g:ia';
        $expectedFooterContents = 'Post expires at EXPIRATIONTIME on ACTIONDATE';
        $expectedFooterStyle = 'color: red;';
        $expectedShortcodeWrapper = 'span';
        $expectedShortcodeWrapperClass = 'future-action';

        $this->simulateDisplaySettingsPostSave([
            'expired-default-date-format' => wp_slash($expectedDateFormat),
            'expired-default-time-format' => wp_slash($expectedTimeFormat),
            'expired-display-footer' => '1',
            'expired-footer-contents' => wp_slash($expectedFooterContents),
            'expired-footer-style' => wp_slash($expectedFooterStyle),
            'shortcode-wrapper' => wp_slash($expectedShortcodeWrapper),
            'shortcode-wrapper-class' => wp_slash($expectedShortcodeWrapperClass),
        ]);

        $settingsFacade = Container::getInstance()->get(ServicesAbstract::SETTINGS);

        $this->assertSame($expectedDateFormat, $settingsFacade->getDefaultDateFormat());
        $this->assertSame($expectedTimeFormat, $settingsFacade->getDefaultTimeFormat());
        $this->assertTrue($settingsFacade->getShowInPostFooter());
        $this->assertSame($expectedFooterContents, $settingsFacade->getFooterContents());
        $this->assertSame($expectedFooterStyle, $settingsFacade->getFooterStyle());
        $this->assertSame($expectedShortcodeWrapper, $settingsFacade->getShortcodeWrapper());
        $this->assertSame($expectedShortcodeWrapperClass, $settingsFacade->getShortcodeWrapperClass());
    }

    public function testMenuDisplaySavedDateFormatRendersLiteralDInFooter(): void
    {
        $dateFormat = 'l \d. j F Y';
        $timestamp = strtotime('2026-08-14 12:00:00');

        $this->simulateDisplaySettingsPostSave([
            'expired-default-date-format' => wp_slash($dateFormat),
            'expired-default-time-format' => wp_slash('g:ia'),
            'expired-display-footer' => '1',
            'expired-footer-contents' => wp_slash('Expires ACTIONDATE'),
            'expired-footer-style' => '',
            'shortcode-wrapper' => 'div',
            'shortcode-wrapper-class' => '',
        ]);

        $dateTimeFacade = Container::getInstance()->get(ServicesAbstract::DATETIME);
        $formattedDate = $dateTimeFacade->getWpDate($dateFormat, $timestamp);

        $this->assertStringContainsString(' d. ', $formattedDate);
        $this->assertStringNotContainsString('\\d', $formattedDate);
    }

    /**
     * @param array<string, mixed> $postData
     */
    private function simulateDisplaySettingsPostSave(array $postData): void
    {
        $_POST = array_merge(
            [
                'expirationdateSaveDisplay' => '1',
                '_postExpiratorMenuDisplay_nonce' => wp_create_nonce('postexpirator_menu_display'),
            ],
            $postData
        );

        PostExpirator_Display::getInstance()->load_tab('display');
    }
}
