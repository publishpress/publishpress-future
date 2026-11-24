<?php

/**
 * Regression tests for future-action scheduling authorization.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

namespace Tests\Modules\Expirator\Regression;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Expirator\CapabilitiesAbstract;
use WP_REST_Request;
use WP_User;

/**
 * @group regression
 */
class ScheduleActionAuthorizationTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @since 4.10.6
     *
     * @param string $role
     * @return int
     */
    private function createUserWithExpireCap(string $role): int
    {
        $userId = (int) $this->factory()->user->create(
            [
                'role' => $role,
            ]
        );

        $user = new WP_User($userId);
        $user->add_cap(CapabilitiesAbstract::EXPIRE_POST);
        wp_set_current_user($userId);

        return $userId;
    }

    /**
     * @since 4.10.6
     *
     * @param int $userId
     * @return int
     */
    private function createOwnDraft(int $userId): int
    {
        return (int) $this->factory()->post->create(
            [
                'post_status' => 'draft',
                'post_type' => 'post',
                'post_author' => $userId,
            ]
        );
    }

    /**
     * @since 4.10.6
     *
     * @param int $postId
     * @param string $action
     * @param string $newStatus
     * @return \WP_REST_Response|\WP_Error
     */
    private function dispatchFutureAction(int $postId, string $action, string $newStatus)
    {
        $nonce = wp_create_nonce('wp_rest');
        $request = new WP_REST_Request('POST', '/wp/v2/posts/' . $postId);
        $request->set_header('X-WP-Nonce', $nonce);
        $_SERVER['HTTP_X_WP_NONCE'] = $nonce;
        $request->set_param(
            'publishpress_future_action',
            [
                'enabled' => true,
                'date' => gmdate('Y-m-d H:i:s', time() + (2 * DAY_IN_SECONDS)),
                'action' => $action,
                'newStatus' => $newStatus,
                'terms' => [],
                'taxonomy' => 'category',
            ]
        );

        $response = rest_get_server()->dispatch($request);
        unset($_SERVER['HTTP_X_WP_NONCE']);

        return $response;
    }

    /**
     * @since 4.10.6
     *
     * @param \WP_REST_Response|\WP_Error $response
     * @return void
     */
    private function assertScheduleForbidden($response): void
    {
        if ($response instanceof \WP_Error) {
            $this->assertSame('rest_cannot_schedule_action', $response->get_error_code());
            $data = $response->get_error_data();
            $this->assertIsArray($data);
            $this->assertSame(403, $data['status']);

            return;
        }

        $this->assertSame(403, $response->get_status());
        $data = $response->get_data();
        $this->assertIsArray($data);
        $this->assertSame('rest_cannot_schedule_action', $data['code']);
    }

    /**
     * @since 4.10.6
     *
     * @return \PublishPress\Future\Modules\Expirator\Interfaces\SchedulerInterface
     */
    private function scheduler()
    {
        return Container::getInstance()->get(ServicesAbstract::EXPIRATION_SCHEDULER);
    }

    /**
     * Contributor with the expire cap cannot publish or stick their own draft over REST.
     *
     * @since 4.10.6
     */
    public function test_contributor_cannot_schedule_publish_or_stick_via_rest(): void
    {
        $userId = $this->createUserWithExpireCap('contributor');

        $publishPostId = $this->createOwnDraft($userId);
        $publishResponse = $this->dispatchFutureAction($publishPostId, 'change-status', 'publish');

        $this->assertScheduleForbidden($publishResponse);
        $this->assertSame('draft', get_post_status($publishPostId));
        $this->assertFalse($this->scheduler()->postIsScheduled($publishPostId));

        $stickPostId = $this->createOwnDraft($userId);
        $stickResponse = $this->dispatchFutureAction($stickPostId, 'stick', '');

        $this->assertScheduleForbidden($stickResponse);
        $this->assertSame('draft', get_post_status($stickPostId));
        $this->assertFalse($this->scheduler()->postIsScheduled($stickPostId));
    }

    /**
     * Author with the expire cap can schedule publish and cannot schedule stick.
     *
     * @since 4.10.6
     */
    public function test_author_can_schedule_publish_but_not_stick(): void
    {
        $userId = $this->createUserWithExpireCap('author');

        $publishPostId = $this->createOwnDraft($userId);
        $publishResponse = $this->dispatchFutureAction($publishPostId, 'change-status', 'publish');

        $this->assertSame(200, $publishResponse->get_status());
        $this->assertSame('draft', get_post_status($publishPostId));
        $this->assertNotFalse($this->scheduler()->postIsScheduled($publishPostId));

        $stickPostId = $this->createOwnDraft($userId);
        $stickResponse = $this->dispatchFutureAction($stickPostId, 'stick', '');

        $this->assertScheduleForbidden($stickResponse);
        $this->assertFalse($this->scheduler()->postIsScheduled($stickPostId));
    }

    /**
     * Administrator can schedule publish and stick.
     *
     * @since 4.10.6
     */
    public function test_administrator_can_schedule_publish_and_stick(): void
    {
        $userId = $this->createUserWithExpireCap('administrator');

        $publishPostId = $this->createOwnDraft($userId);
        $publishResponse = $this->dispatchFutureAction($publishPostId, 'change-status', 'publish');

        $this->assertSame(200, $publishResponse->get_status());
        $this->assertNotFalse($this->scheduler()->postIsScheduled($publishPostId));

        $stickPostId = $this->createOwnDraft($userId);
        $stickResponse = $this->dispatchFutureAction($stickPostId, 'stick', '');

        $this->assertSame(200, $stickResponse->get_status());
        $this->assertNotFalse($this->scheduler()->postIsScheduled($stickPostId));
    }

    /**
     * Direct scheduler calls use the same cap check for a logged-in contributor.
     *
     * @since 4.10.6
     */
    public function test_scheduler_blocks_contributor_publish_but_allows_draft(): void
    {
        $userId = $this->createUserWithExpireCap('contributor');
        $scheduler = $this->scheduler();
        $timestamp = time() + (2 * DAY_IN_SECONDS);

        $publishPostId = $this->createOwnDraft($userId);
        $scheduler->schedule(
            $publishPostId,
            $timestamp,
            [
                'expireType' => 'change-status',
                'newStatus' => 'publish',
            ]
        );

        $this->assertFalse($scheduler->postIsScheduled($publishPostId));
        $this->assertSame('draft', get_post_status($publishPostId));

        $draftPostId = $this->createOwnDraft($userId);
        $scheduler->schedule(
            $draftPostId,
            $timestamp,
            [
                'expireType' => 'change-status',
                'newStatus' => 'draft',
            ]
        );

        $this->assertNotFalse($scheduler->postIsScheduled($draftPostId));

        $legacyPostId = $this->createOwnDraft($userId);
        $scheduler->schedule(
            $legacyPostId,
            $timestamp,
            [
                'expireType' => 'draft',
            ]
        );

        $this->assertNotFalse($scheduler->postIsScheduled($legacyPostId));
    }
}
