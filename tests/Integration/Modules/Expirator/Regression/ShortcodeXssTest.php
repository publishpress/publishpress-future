<?php

/**
 * Regression tests for the futureaction shortcode XSS vulnerability.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

namespace Tests\Modules\Expirator\Regression;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract;

/**
 * @group regression
 */
class ShortcodeXssTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * Creates a published post with expiration enabled and returns its ID.
     *
     * @since 1.0.0
     *
     * @return int
     */
    private function createPostWithExpirationEnabled(): int
    {
        $postId = $this->factory()->post->create(
            [
                'post_status' => 'publish',
                'post_type' => 'post',
            ]
        );

        $options = [
            'expireType' => 'change-status',
            'newStatus' => 'draft',
            'id' => $postId,
        ];

        $timestamp = strtotime('+2 days');

        do_action(
            HooksAbstract::ACTION_SCHEDULE_POST_EXPIRATION,
            $postId,
            $timestamp,
            $options
        );

        return $postId;
    }

    /**
     * Asserts that a wrapper attribute containing an onerror XSS payload does
     * not produce output with the injected event handler attribute.
     *
     * @since 1.0.0
     */
    public function test_wrapper_attribute_with_xss_payload_does_not_contain_onerror(): void
    {
        $postId = $this->createPostWithExpirationEnabled();

        $output = do_shortcode(
            sprintf(
                '[futureaction post_id="%d" wrapper="img src=x onerror=alert(document.domain)" class="ppf"]',
                $postId
            )
        );

        $this->assertStringNotContainsString('onerror=', $output);
        $this->assertStringNotContainsString('alert(', $output);
    }

    /**
     * Asserts that a wrapper attribute containing an onmouseover XSS payload
     * does not produce output with the injected event handler attribute.
     *
     * @since 1.0.0
     */
    public function test_wrapper_attribute_with_script_tag_does_not_execute(): void
    {
        $postId = $this->createPostWithExpirationEnabled();

        $output = do_shortcode(
            sprintf(
                '[futureaction post_id="%d" wrapper="div onmouseover=alert(1)" class="ppf"]',
                $postId
            )
        );

        $this->assertStringNotContainsString('onmouseover=', $output);
        $this->assertStringNotContainsString('alert(', $output);
    }

    /**
     * Asserts that a valid HTML tag name in the wrapper attribute is rendered
     * correctly, verifying that legitimate use of the wrapper attribute still
     * works after the XSS fix.
     *
     * @since 1.0.0
     */
    public function test_wrapper_attribute_with_valid_html_tag_is_rendered(): void
    {
        $postId = $this->createPostWithExpirationEnabled();

        $output = do_shortcode(
            sprintf(
                '[futureaction post_id="%d" wrapper="span" class="ppf"]',
                $postId
            )
        );

        $this->assertStringContainsString('<span', $output);
    }

    /**
     * Literal date-format characters must not execute inside a script wrapper element.
     *
     * @since 4.10.6
     */
    public function test_script_wrapper_falls_back_to_div_with_escaped_literal_date_text(): void
    {
        $postId = $this->createPostWithExpirationEnabled();
        $literalAlertFormat = '\\\\a\\\\l\\\\e\\\\r\\\\t\\\\(1\\\\)';

        $output = do_shortcode(
            sprintf(
                '[futureaction post_id="%d" wrapper="script" class="ppf" type="date" dateformat="%s"]',
                $postId,
                $literalAlertFormat
            )
        );

        $this->assertStringContainsString('<div class="ppf">', $output);
        $this->assertStringContainsString('alert(1)', $output);
        $this->assertStringNotContainsString('<script', strtolower($output));
    }

    /**
     * @since 4.10.6
     */
    public function test_style_wrapper_falls_back_to_div_with_escaped_literal_date_text(): void
    {
        $postId = $this->createPostWithExpirationEnabled();
        $literalAlertFormat = '\\\\a\\\\l\\\\e\\\\r\\\\t\\\\(1\\\\)';

        $output = do_shortcode(
            sprintf(
                '[futureaction post_id="%d" wrapper="style" class="ppf" type="date" dateformat="%s"]',
                $postId,
                $literalAlertFormat
            )
        );

        $this->assertStringContainsString('<div class="ppf">', $output);
        $this->assertStringContainsString('alert(1)', $output);
        $this->assertStringNotContainsString('<style', strtolower($output));
    }

    /**
     * @since 4.10.6
     */
    public function test_timeformat_literal_payload_does_not_use_raw_text_elements(): void
    {
        $postId = $this->createPostWithExpirationEnabled();
        $literalAlertFormat = '\\\\a\\\\l\\\\e\\\\r\\\\t\\\\(1\\\\)';

        $output = do_shortcode(
            sprintf(
                '[futureaction post_id="%d" wrapper="script" class="ppf" type="time" timeformat="%s"]',
                $postId,
                $literalAlertFormat
            )
        );

        $this->assertStringContainsString('<div class="ppf">', $output);
        $this->assertStringContainsString('alert(1)', $output);
        $this->assertStringNotContainsString('<script', strtolower($output));
    }

    /**
     * @since 4.10.6
     */
    public function test_postexpirator_alias_applies_wrapper_allow_list(): void
    {
        $postId = $this->createPostWithExpirationEnabled();
        $literalAlertFormat = '\\\\a\\\\l\\\\e\\\\r\\\\t\\\\(1\\\\)';

        $output = do_shortcode(
            sprintf(
                '[postexpirator post_id="%d" wrapper="script" class="ppf" type="date" dateformat="%s"]',
                $postId,
                $literalAlertFormat
            )
        );

        $this->assertStringContainsString('<div class="ppf">', $output);
        $this->assertStringNotContainsString('<script', strtolower($output));
    }

    /**
     * @since 4.10.6
     */
    public function test_wrapper_attribute_is_case_insensitive_for_allowed_tags(): void
    {
        $postId = $this->createPostWithExpirationEnabled();

        $output = do_shortcode(
            sprintf(
                '[futureaction post_id="%d" wrapper="SPAN" class="ppf" type="date" dateformat="Y-m-d"]',
                $postId
            )
        );

        $this->assertStringContainsString('<span class="ppf">', $output);
    }

    /**
     * @since 4.10.6
     */
    public function test_unwrapped_output_escapes_markup_from_date_format(): void
    {
        $postId = $this->createPostWithExpirationEnabled();
        $settingsFacade = Container::getInstance()->get(ServicesAbstract::SETTINGS);
        $settingsFacade->setShortcodeWrapper('');

        $markupFormat = '\\\\<\\\\s\\\\c\\\\r\\\\i\\\\p\\\\t\\\\>\\\\a\\\\l\\\\e\\\\r\\\\t\\\\(1\\\\)\\\\<\\\\/\\\\s\\\\c\\\\r\\\\i\\\\p\\\\t\\\\>';

        $output = do_shortcode(
            sprintf(
                '[futureaction post_id="%d" type="date" dateformat="%s"]',
                $postId,
                $markupFormat
            )
        );

        $this->assertStringContainsString('&lt;script&gt;', $output);
        $this->assertStringNotContainsString('<script', strtolower($output));
    }

    /**
     * @since 4.10.6
     */
    public function test_settings_save_rejects_unsafe_shortcode_wrapper(): void
    {
        $settingsFacade = Container::getInstance()->get(ServicesAbstract::SETTINGS);
        $settingsFacade->setShortcodeWrapper('script');

        $this->assertSame('div', $settingsFacade->getShortcodeWrapper());
    }

    /**
     * @since 4.10.6
     */
    public function test_allowed_wrapper_tags_still_render(): void
    {
        $postId = $this->createPostWithExpirationEnabled();

        foreach (['p', 'div'] as $tag) {
            $output = do_shortcode(
                sprintf(
                    '[futureaction post_id="%d" wrapper="%s" class="ppf" type="date" dateformat="Y-m-d"]',
                    $postId,
                    $tag
                )
            );

            $this->assertStringContainsString(
                sprintf('<%s class="ppf">', $tag),
                $output,
                sprintf('Expected <%s> wrapper in output.', $tag)
            );
        }
    }
}
