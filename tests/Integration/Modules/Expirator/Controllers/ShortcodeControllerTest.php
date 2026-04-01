<?php

namespace Tests\Modules\Expirator\Controllers;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;

class ShortcodeControllerTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    private const EXPIRATION_UNIX_TS = 1742239607;

    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @return int Post ID with expiration scheduled at EXPIRATION_UNIX_TS.
     */
    private function createPostWithScheduledExpiration(): int
    {
        $postId = $this->factory()->post->create();

        $scheduler = Container::getInstance()->get(ServicesAbstract::EXPIRATION_SCHEDULER);
        $scheduler->schedule($postId, self::EXPIRATION_UNIX_TS, [
            'expireType' => ExpirationActionsAbstract::POST_STATUS_TO_DRAFT,
            'new_status' => 'draft',
        ]);

        return $postId;
    }

    public function test_futureaction_span_wrapper_outputs_span_with_formatted_date(): void
    {
        $postId = $this->createPostWithScheduledExpiration();

        $shortcode = sprintf(
            '[futureaction post_id="%d" wrapper="span" class="test-shortcode-class" type="date" dateformat="Y-m-d"]',
            $postId
        );

        $output = do_shortcode($shortcode);

        $this->assertNotFalse($output, 'Shortcode should render when expiration is scheduled.');
        $this->assertIsString($output);
        $this->assertStringContainsString('<span class="test-shortcode-class">', $output);
        $this->assertStringContainsString('2025-03-17', $output);
        $this->assertStringContainsString('</span>', $output);
    }

    public function test_futureaction_unsafe_wrapper_falls_back_to_div_without_injected_markup(): void
    {
        $postId = $this->createPostWithScheduledExpiration();

        // CVE-2026-5247 / Wordfence PoC: spaces and event handlers were injected into the
        // opening tag when wrapper was only passed through esc_html(). tag_escape() must
        // reject values that change under sanitization. Do not use "div><script" here: that
        // literal contains "<script", so unparsed shortcode output would fail a "<script" assertion.
        $maliciousWrapper = 'div autofocus tabindex=1 onfocus=alert(2026)';
        $this->assertNotSame(
            $maliciousWrapper,
            tag_escape($maliciousWrapper),
            'Precondition: wrapper must be altered by tag_escape to exercise fallback to div.'
        );

        $shortcode = sprintf(
            '[futureaction post_id="%d" wrapper="%s" class="test-shortcode-class" type="date" dateformat="Y-m-d"]',
            $postId,
            $maliciousWrapper
        );

        $output = do_shortcode($shortcode);

        $this->assertNotFalse(
            $output,
            'Shortcode must render HTML when the post has a scheduled expiration '
            . '(false often means expiration is disabled or not persisted for this post).'
        );
        $this->assertIsString(
            $output,
            'Shortcode output must be a string after rendering.'
        );
        $this->assertStringContainsString(
            '<div class="test-shortcode-class">',
            $output,
            'Unsafe wrapper values must fall back to a plain div with the shortcode class '
            . '(expected opening tag <div class="test-shortcode-class">).'
        );
        $this->assertStringContainsString(
            '</div>',
            $output,
            'Fallback wrapper must close with </div>.'
        );
        $this->assertStringNotContainsString(
            'autofocus',
            $output,
            'Rendered HTML must not leak the malicious wrapper token "autofocus" (CVE-2026-5247).'
        );
        $this->assertStringNotContainsString(
            'onfocus=',
            $output,
            'Rendered HTML must not contain an injected onfocus handler from the wrapper attribute.'
        );
        $this->assertStringNotContainsString(
            'tabindex=',
            $output,
            'Rendered HTML must not contain an injected tabindex attribute from the wrapper payload.'
        );
        $this->assertStringContainsString(
            '2025-03-17',
            $output,
            'Formatted expiration date for the test timestamp must appear in output '
            . '(same timezone as the integration test environment).'
        );
    }
}
