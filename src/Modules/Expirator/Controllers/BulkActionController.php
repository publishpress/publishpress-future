<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Controllers;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\InitializableInterface;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use PublishPress\Future\Modules\Expirator\Models\PostTypesModel;
use PublishPress\Future\Modules\Expirator\Models\CurrentUserModel;

defined('ABSPATH') or die('Direct access not allowed.');

class BulkActionController implements InitializableInterface
{
    public const BULK_ACTION_SYNC = 'sync_scheduler_with_post_meta';

    public const NOTICE_SUCCESS = 'publishpress-future-posts-synced';

    public const NOTICE_NO_POSTS_SELECTED = 'publishpress-future-no-posts-selected';

    /**
     * @var HookableInterface
     */
    private $hooks;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    /**
     * @var NoticeFacade
     */
    private $notices;

    /**
     * @var CurrentUserModel
     */
    private $currentUserModel;

    /**
     * @param HookableInterface $hooksFacade
     * @param callable $expirablePostModelFactory
     * @param \PublishPress\Future\Framework\WordPress\Facade\NoticeFacade $notices
     * @param \Closure $currentUserModelFactory
     */
    public function __construct(
        HookableInterface $hooksFacade,
        $expirablePostModelFactory,
        $notices,
        \Closure $currentUserModelFactory
    ) {
        $this->hooks = $hooksFacade;
        $this->expirablePostModelFactory = $expirablePostModelFactory;
        $this->notices = $notices;
        $this->currentUserModel = $currentUserModelFactory();
    }

    public function initialize()
    {
        if (! $this->currentUserModel->userCanExpirePosts()) {
            return;
        }

        $this->addHooks();
        $this->registerNotices();
    }

    private function addHooks()
    {
        $container = \PublishPress\Future\Core\DI\Container::getInstance();
        $postTypes = new PostTypesModel($container);
        $activatedPostTypes = $postTypes->getActivatedPostTypes();

        foreach ($activatedPostTypes as $postType) {
            $this->hooks->addFilter(
                'bulk_actions-edit-' . $postType,
                function ($actions) use ($postType) {
                    return $this->filterBulkActions($actions, $postType);
                }
            );

            $this->hooks->addFilter(
                'handle_bulk_actions-edit-' . $postType,
                [$this, 'handleBulkActionSync'],
                10,
                3
            );
        }
    }

    private function registerNotices()
    {
        $this->notices->registerErrorNotice(
            self::NOTICE_NO_POSTS_SELECTED,
            __('No posts selected. Unable to sync Future Actions.', 'post-expirator')
        );

        $this->notices->registerSuccessNotice(
            self::NOTICE_SUCCESS,
            __('Future Actions successfully synced with Post Metadata.', 'post-expirator')
        );
    }

    /**
     * Add the sync bulk action to the post list table.
     *
     * @param array  $actions  Existing bulk actions.
     * @param string $postType Post type for the current list table.
     *
     * @return array
     */
    public function filterBulkActions($actions, $postType)
    {
        $displayTheOption = $this->hooks->applyFilters(
            HooksAbstract::FILTER_DISPLAY_BULK_ACTION_SYNC,
            true,
            $postType
        );

        if ($displayTheOption) {
            $actions[self::BULK_ACTION_SYNC] = __('Update Future Actions from Post Metadata', 'post-expirator');
        }

        return $actions;
    }

    public function handleBulkActionSync($redirectTo, $doAction, $postIds)
    {
        if ($doAction !== self::BULK_ACTION_SYNC) {
            return $redirectTo;
        }

        if (! $this->currentUserModel->userCanExpirePosts()) {
            return $redirectTo;
        }

        if (empty($postIds)) {
            return add_query_arg('notice', self::NOTICE_NO_POSTS_SELECTED, $redirectTo);
        }

        $expirablePostModelFactory = $this->expirablePostModelFactory;
        $syncedCount = 0;

        foreach ($postIds as $postId) {
            if (! $this->currentUserModel->userCanEditPost($postId)) {
                continue;
            }

            $postModel = $expirablePostModelFactory($postId);
            $postModel->syncScheduleWithPostMeta();
            $syncedCount++;
        }

        if ($syncedCount === 0) {
            return add_query_arg('notice', self::NOTICE_NO_POSTS_SELECTED, $redirectTo);
        }

        return add_query_arg('notice', self::NOTICE_SUCCESS, $redirectTo);
    }
}
