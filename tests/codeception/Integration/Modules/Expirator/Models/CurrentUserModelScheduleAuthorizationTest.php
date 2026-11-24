<?php

/**
 * Schedule authorization for trash status and taxonomy actions.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace Tests\Modules\Expirator\Models;

use PublishPress\Future\Modules\Expirator\CapabilitiesAbstract;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\Models\CurrentUserModel;
use lucatume\WPBrowser\TestCase\WPTestCase;

class CurrentUserModelScheduleAuthorizationTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    private const ROLE_EDIT_NO_DELETE = 'ppfuture_edit_no_delete';

    public function setUp(): void
    {
        parent::setUp();

        wp_set_current_user(0);
    }

    public function tearDown(): void
    {
        wp_set_current_user(0);
        remove_role(self::ROLE_EDIT_NO_DELETE);

        parent::tearDown();
    }

    /**
     * @since 4.12.0
     */
    public function test_should_refuse_trash_status_without_delete_post(): void
    {
        $userId = $this->createEditorWithoutDelete();
        $postId = $this->createDraftFor($userId);
        wp_set_current_user($userId);

        $this->assertTrue(current_user_can('edit_post', $postId));
        $this->assertTrue(current_user_can(CapabilitiesAbstract::EXPIRE_POST));
        $this->assertFalse(current_user_can('delete_post', $postId));

        $model = new CurrentUserModel();

        $this->assertFalse(
            $model->userCanScheduleAction(
                $postId,
                ExpirationActionsAbstract::CHANGE_POST_STATUS,
                ExpirationActionsAbstract::POST_STATUS_TO_TRASH
            )
        );
    }

    /**
     * @since 4.12.0
     */
    public function test_should_allow_trash_status_when_user_can_delete_post(): void
    {
        $userId = (int) $this->factory()->user->create(['role' => 'author']);
        $user = get_userdata($userId);
        $user->add_cap(CapabilitiesAbstract::EXPIRE_POST);
        $postId = $this->createDraftFor($userId);
        wp_set_current_user($userId);

        $this->assertTrue(current_user_can('edit_post', $postId));
        $this->assertTrue(current_user_can('delete_post', $postId));

        $model = new CurrentUserModel();

        $this->assertTrue(
            $model->userCanScheduleAction(
                $postId,
                ExpirationActionsAbstract::CHANGE_POST_STATUS,
                ExpirationActionsAbstract::POST_STATUS_TO_TRASH
            )
        );
    }

    /**
     * @since 4.12.0
     */
    public function test_should_refuse_taxonomy_action_when_taxonomy_is_not_on_post_type(): void
    {
        $taxonomy = 'ppfuture_page_only_tax';
        register_taxonomy($taxonomy, 'page', ['public' => false]);

        try {
            $userId = (int) $this->factory()->user->create(['role' => 'editor']);
            $postId = $this->createDraftFor($userId);
            wp_set_current_user($userId);

            $model = new CurrentUserModel();

            $this->assertFalse(
                $model->userCanScheduleAction(
                    $postId,
                    ExpirationActionsAbstract::POST_CATEGORY_ADD,
                    '',
                    $taxonomy
                )
            );
        } finally {
            unregister_taxonomy($taxonomy);
        }
    }

    /**
     * @since 4.12.0
     */
    public function test_should_refuse_taxonomy_action_without_assign_terms(): void
    {
        $taxonomy = 'ppfuture_locked_tax';
        register_taxonomy(
            $taxonomy,
            'post',
            [
                'public' => false,
                'capabilities' => [
                    'manage_terms' => 'manage_ppfuture_locked_tax',
                    'edit_terms' => 'edit_ppfuture_locked_tax',
                    'delete_terms' => 'delete_ppfuture_locked_tax',
                    'assign_terms' => 'assign_ppfuture_locked_tax',
                ],
            ]
        );

        try {
            $userId = (int) $this->factory()->user->create(['role' => 'editor']);
            $postId = $this->createDraftFor($userId);
            wp_set_current_user($userId);

            $this->assertFalse(current_user_can('assign_ppfuture_locked_tax'));

            $model = new CurrentUserModel();

            $this->assertFalse(
                $model->userCanScheduleAction(
                    $postId,
                    ExpirationActionsAbstract::POST_CATEGORY_ADD,
                    '',
                    $taxonomy
                )
            );
        } finally {
            unregister_taxonomy($taxonomy);
        }
    }

    /**
     * Stock category assign_terms is edit_posts. An editor has that cap.
     *
     * @since 4.12.0
     */
    public function test_should_allow_category_add_when_user_can_assign_terms(): void
    {
        $userId = (int) $this->factory()->user->create(['role' => 'editor']);
        $postId = $this->createDraftFor($userId);
        wp_set_current_user($userId);

        $taxonomy = get_taxonomy('category');
        $this->assertInstanceOf(\WP_Taxonomy::class, $taxonomy);
        $this->assertTrue(is_object_in_taxonomy('post', 'category'));
        $this->assertTrue(current_user_can($taxonomy->cap->assign_terms));

        $model = new CurrentUserModel();

        $this->assertTrue(
            $model->userCanScheduleAction(
                $postId,
                ExpirationActionsAbstract::POST_CATEGORY_ADD,
                '',
                'category'
            )
        );
    }

    /**
     * @since 4.12.0
     *
     * @return int
     */
    private function createEditorWithoutDelete(): int
    {
        remove_role(self::ROLE_EDIT_NO_DELETE);
        add_role(
            self::ROLE_EDIT_NO_DELETE,
            'PP Future Edit No Delete',
            [
                'read' => true,
                'edit_posts' => true,
                CapabilitiesAbstract::EXPIRE_POST => true,
            ]
        );

        return (int) $this->factory()->user->create(['role' => self::ROLE_EDIT_NO_DELETE]);
    }

    /**
     * @since 4.12.0
     *
     * @param int $userId
     * @return int
     */
    private function createDraftFor(int $userId): int
    {
        return (int) $this->factory()->post->create(
            [
                'post_author' => $userId,
                'post_status' => 'draft',
                'post_type' => 'post',
            ]
        );
    }
}
