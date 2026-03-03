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
}
