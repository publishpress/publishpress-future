<?php

/**
 * Regression tests for unauthenticated disclosure of the publishpress_future_action REST field.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

namespace Tests\Modules\Expirator\Regression;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use WP_REST_Request;
use WP_REST_Response;

/**
 * @group regression
 */
class RestFieldFutureActionDisclosureTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * Creates a published post with expiration enabled and returns its ID.
     *
     * @since 4.10.6
     *
     * @return int
     */
    private function createPostWithExpirationEnabled(int $authorId = 0): int
    {
        $postArgs = [
            'post_status' => 'publish',
            'post_type' => 'post',
        ];

        if ($authorId > 0) {
            $postArgs['post_author'] = $authorId;
        }

        $postId = $this->factory()->post->create($postArgs);

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

        return (int) $postId;
    }

    /**
     * Dispatches a REST request and returns the response.
     *
     * @since 4.10.6
     *
     * @param string $route
     * @param array<string, mixed> $queryParams
     * @return WP_REST_Response
     */
    private function dispatchGet(string $route, array $queryParams = []): WP_REST_Response
    {
        $request = new WP_REST_Request('GET', $route);

        if ($queryParams !== []) {
            $request->set_query_params($queryParams);
        }

        $response = rest_do_request($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        return $response;
    }

    /**
     * Finds one post in a collection response by ID.
     *
     * @since 4.10.6
     *
     * @param mixed $data
     * @param int $postId
     * @return array<string, mixed>|null
     */
    private function findPostInCollection($data, int $postId): ?array
    {
        if (! is_array($data)) {
            return null;
        }

        foreach ($data as $post) {
            if (is_array($post) && isset($post['id']) && (int) $post['id'] === $postId) {
                return $post;
            }
        }

        return null;
    }

    /**
     * Logged-out single-post view must not include the Future Action field.
     *
     * @since 4.10.6
     */
    public function test_should_omit_future_action_field_for_logged_out_single_scheduled_post(): void
    {
        $postId = $this->createPostWithExpirationEnabled();
        wp_set_current_user(0);

        $response = $this->dispatchGet('/wp/v2/posts/' . $postId);
        $data = $response->get_data();

        $this->assertSame(200, $response->get_status());
        $this->assertIsArray($data);
        $this->assertArrayNotHasKey('publishpress_future_action', $data);
    }

    /**
     * Logged-out collection requests must omit the field even when it is named in _fields.
     *
     * @since 4.10.6
     */
    public function test_should_omit_future_action_field_for_logged_out_collection_fields_query(): void
    {
        $postId = $this->createPostWithExpirationEnabled();
        wp_set_current_user(0);

        $response = $this->dispatchGet(
            '/wp/v2/posts',
            [
                '_fields' => 'id,publishpress_future_action',
                'include' => [$postId],
            ]
        );
        $data = $response->get_data();
        $post = $this->findPostInCollection($data, $postId);

        $this->assertSame(200, $response->get_status());
        $this->assertIsArray($post);
        $this->assertArrayNotHasKey('publishpress_future_action', $post);
    }

    /**
     * Logged-out view of a published post with no Future Action must omit the field.
     *
     * @since 4.10.6
     */
    public function test_should_omit_future_action_field_for_logged_out_post_without_schedule(): void
    {
        $postId = (int) $this->factory()->post->create(
            [
                'post_status' => 'publish',
                'post_type' => 'post',
            ]
        );
        wp_set_current_user(0);

        $response = $this->dispatchGet('/wp/v2/posts/' . $postId);
        $data = $response->get_data();

        $this->assertSame(200, $response->get_status());
        $this->assertIsArray($data);
        $this->assertArrayNotHasKey('publishpress_future_action', $data);
    }

    /**
     * Editors reading context=edit receive the scheduled action stored on the post.
     *
     * @since 4.10.6
     */
    public function test_should_include_scheduled_action_for_editor_in_edit_context(): void
    {
        $postId = $this->createPostWithExpirationEnabled();
        $editorId = $this->factory()->user->create(['role' => 'editor']);
        wp_set_current_user($editorId);

        $factory = Container::getInstance()->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
        $postModel = $factory($postId);
        $expectedAction = $postModel->getExpirationType();

        $response = $this->dispatchGet(
            '/wp/v2/posts/' . $postId,
            ['context' => 'edit']
        );
        $data = $response->get_data();

        $this->assertSame(200, $response->get_status());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('publishpress_future_action', $data);
        $this->assertIsArray($data['publishpress_future_action']);
        $this->assertSame($expectedAction, $data['publishpress_future_action']['action']);
        $this->assertSame(ExpirationActionsAbstract::CHANGE_POST_STATUS, $expectedAction);
    }

    /**
     * Default view context omits the field even for a user who can edit the post.
     *
     * @since 4.10.6
     */
    public function test_should_omit_future_action_field_for_editor_in_view_context(): void
    {
        $postId = $this->createPostWithExpirationEnabled();
        $editorId = $this->factory()->user->create(['role' => 'editor']);
        wp_set_current_user($editorId);

        $response = $this->dispatchGet('/wp/v2/posts/' . $postId);
        $data = $response->get_data();

        $this->assertSame(200, $response->get_status());
        $this->assertIsArray($data);
        $this->assertArrayNotHasKey('publishpress_future_action', $data);
    }

    /**
     * An author with edit_posts, but not edit_post for this item, must not see the field.
     *
     * Core does not 403 the collection: WP_REST_Posts_Controller::get_items() skips posts
     * that fail check_update_permission(), so the other author's post is omitted and the
     * field callback never runs. Single-post context=edit without edit_post is a core 403.
     *
     * @since 4.10.6
     */
    public function test_should_hide_future_action_when_author_cannot_edit_post(): void
    {
        $ownerId = (int) $this->factory()->user->create(['role' => 'author']);
        $postId = $this->createPostWithExpirationEnabled($ownerId);
        $otherAuthorId = (int) $this->factory()->user->create(['role' => 'author']);
        wp_set_current_user($otherAuthorId);

        $response = $this->dispatchGet(
            '/wp/v2/posts',
            [
                'context' => 'edit',
                'include' => [$postId],
            ]
        );

        $this->assertSame(200, $response->get_status());
        $this->assertNull($this->findPostInCollection($response->get_data(), $postId));

        $single = $this->dispatchGet(
            '/wp/v2/posts/' . $postId,
            ['context' => 'edit']
        );

        $this->assertSame(403, $single->get_status());
    }
}
