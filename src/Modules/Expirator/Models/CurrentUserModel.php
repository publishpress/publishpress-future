<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Models;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Framework\WordPress\Models\CurrentUserModel as FrameworkCurrentUserModel;
use PublishPress\Future\Modules\Expirator\CapabilitiesAbstract as Capabilities;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;

\defined('ABSPATH') || exit;

class CurrentUserModel extends FrameworkCurrentUserModel
{
    /**
     * @return bool
     */
    public function userCanExpirePosts(): bool
    {
        return current_user_can(Capabilities::EXPIRE_POST);
    }

    public function userCanEditPost($postId)
    {
        return current_user_can('edit_post', $postId);
    }

    public function userCanReadPost($postId)
    {
        return current_user_can('read_post', $postId);
    }

    /**
     * Whether the live current user may schedule this action for the post.
     *
     * Does not check publishpress_future_expire_post. Entry points already do.
     *
     * @since 4.10.6
     *
     * @param int $postId
     * @param string $expireType
     * @param string $newStatus
     * @param string $taxonomy Taxonomy for category actions. Empty fails closed.
     * @return bool
     */
    public function userCanScheduleAction(
        int $postId,
        string $expireType,
        string $newStatus,
        string $taxonomy = ''
    ): bool {
        if ($expireType === ExpirationActionsAbstract::POST_STATUS_TO_DRAFT) {
            $expireType = ExpirationActionsAbstract::CHANGE_POST_STATUS;
            $newStatus = ExpirationActionsAbstract::POST_STATUS_TO_DRAFT;
        }

        if ($expireType === ExpirationActionsAbstract::POST_STATUS_TO_PRIVATE) {
            $expireType = ExpirationActionsAbstract::CHANGE_POST_STATUS;
            $newStatus = ExpirationActionsAbstract::POST_STATUS_TO_PRIVATE;
        }

        if ($expireType === ExpirationActionsAbstract::POST_STATUS_TO_TRASH) {
            $expireType = ExpirationActionsAbstract::CHANGE_POST_STATUS;
            $newStatus = ExpirationActionsAbstract::POST_STATUS_TO_TRASH;
        }

        $postType = get_post_type($postId);
        if (! is_string($postType) || $postType === '') {
            return false;
        }

        $postTypeObject = get_post_type_object($postType);
        if (! is_object($postTypeObject)) {
            return false;
        }

        if ($expireType === ExpirationActionsAbstract::CHANGE_POST_STATUS) {
            return $this->userCanScheduleStatusChange($postType, $postTypeObject, $newStatus, $postId);
        }

        if (
            $expireType === ExpirationActionsAbstract::STICK_POST
            || $expireType === ExpirationActionsAbstract::UNSTICK_POST
        ) {
            return $this->currentUserHasPostTypeCap($postTypeObject, 'edit_others_posts');
        }

        if ($expireType === ExpirationActionsAbstract::DELETE_POST) {
            return current_user_can('delete_post', $postId);
        }

        $actionsModel = $this->getExpirationActionsModel();
        if (! $actionsModel instanceof ExpirationActionsModel) {
            return false;
        }

        $actions = $actionsModel->getActions($postType);
        if (! isset($actions[$expireType])) {
            return false;
        }

        if ($this->isTaxonomyExpirationAction($expireType)) {
            return $this->userCanAssignTaxonomyTerms($postType, $taxonomy);
        }

        return true;
    }

    /**
     * Drop action and status choices the current user cannot schedule.
     *
     * List screens pass post ID 0. Delete and the trash status then use the
     * post type delete_posts cap. A known post ID uses delete_post.
     *
     * @since 4.10.6
     *
     * @param array<int, array<string, string>> $actions
     * @param array<int, array<string, string>> $statuses
     * @param string $postType
     * @param int $postId
     * @return array{actions: array<int, array<string, string>>, statuses: array<int, array<string, string>>}
     */
    public function filterScheduleSelectOptions(array $actions, array $statuses, string $postType, int $postId = 0): array
    {
        $postTypeObject = $postType === '' ? null : get_post_type_object($postType);
        $canEditOthers = is_object($postTypeObject)
            && $this->currentUserHasPostTypeCap($postTypeObject, 'edit_others_posts');
        $canPublish = is_object($postTypeObject)
            && $this->currentUserHasPostTypeCap($postTypeObject, 'publish_posts');

        if ($postId > 0) {
            $canDelete = current_user_can('delete_post', $postId);
        } else {
            $canDelete = is_object($postTypeObject)
                && $this->currentUserHasPostTypeCap($postTypeObject, 'delete_posts');
        }

        $deniedActions = [];
        if (! $canEditOthers) {
            $deniedActions[] = ExpirationActionsAbstract::STICK_POST;
            $deniedActions[] = ExpirationActionsAbstract::UNSTICK_POST;
        }
        if (! $canDelete) {
            $deniedActions[] = ExpirationActionsAbstract::DELETE_POST;
        }

        $deniedStatuses = [];
        if (! $canPublish) {
            $deniedStatuses[] = 'publish';
            $deniedStatuses[] = 'private';
        }
        if (! $canDelete) {
            $deniedStatuses[] = ExpirationActionsAbstract::POST_STATUS_TO_TRASH;
        }

        return [
            'actions' => $this->removeSelectOptionValues($actions, $deniedActions),
            'statuses' => $this->removeSelectOptionValues($statuses, $deniedStatuses),
        ];
    }

    /**
     * @since 4.10.6
     *
     * @param string $postType
     * @param object $postTypeObject
     * @param string $newStatus
     * @param int $postId Post ID 0 uses the post type delete_posts cap for trash.
     * @return bool
     */
    private function userCanScheduleStatusChange(
        string $postType,
        object $postTypeObject,
        string $newStatus,
        int $postId
    ): bool {
        $actionsModel = $this->getExpirationActionsModel();
        if (! $actionsModel instanceof ExpirationActionsModel) {
            return false;
        }

        $statuses = $actionsModel->getStatusesForPostType($postType);
        if ($newStatus === '' || ! isset($statuses[$newStatus])) {
            return false;
        }

        if (in_array($newStatus, ['publish', 'private', 'future'], true)) {
            return $this->currentUserHasPostTypeCap($postTypeObject, 'publish_posts');
        }

        if ($newStatus === ExpirationActionsAbstract::POST_STATUS_TO_TRASH) {
            if ($postId > 0) {
                return current_user_can('delete_post', $postId);
            }

            return $this->currentUserHasPostTypeCap($postTypeObject, 'delete_posts');
        }

        return true;
    }

    /**
     * Category actions need assign_terms on a taxonomy registered for the post type.
     *
     * @since 4.12.0
     *
     * @param string $expireType
     * @return bool
     */
    private function isTaxonomyExpirationAction(string $expireType): bool
    {
        return in_array(
            $expireType,
            [
                ExpirationActionsAbstract::POST_CATEGORY_SET,
                ExpirationActionsAbstract::POST_CATEGORY_ADD,
                ExpirationActionsAbstract::POST_CATEGORY_REMOVE,
                ExpirationActionsAbstract::POST_CATEGORY_REMOVE_ALL,
            ],
            true
        );
    }

    /**
     * @since 4.12.0
     *
     * @param string $postType
     * @param string $taxonomy
     * @return bool
     */
    private function userCanAssignTaxonomyTerms(string $postType, string $taxonomy): bool
    {
        if ($taxonomy === '') {
            return false;
        }

        $taxonomyObject = get_taxonomy($taxonomy);
        if (! is_object($taxonomyObject) || ! is_object_in_taxonomy($postType, $taxonomy)) {
            return false;
        }

        if (
            ! isset($taxonomyObject->cap->assign_terms)
            || ! is_string($taxonomyObject->cap->assign_terms)
            || $taxonomyObject->cap->assign_terms === ''
        ) {
            return false;
        }

        return current_user_can($taxonomyObject->cap->assign_terms);
    }

    /**
     * @since 4.10.6
     *
     * @param object $postTypeObject
     * @param string $capName
     * @return bool
     */
    private function currentUserHasPostTypeCap(object $postTypeObject, string $capName): bool
    {
        $capability = $this->getPostTypeCapability($postTypeObject, $capName);
        if ($capability === '') {
            return false;
        }

        return current_user_can($capability);
    }

    /**
     * @since 4.10.6
     *
     * @param object $postTypeObject
     * @param string $capName
     * @return string
     */
    private function getPostTypeCapability(object $postTypeObject, string $capName): string
    {
        $postTypeVars = get_object_vars($postTypeObject);
        if (! isset($postTypeVars['cap']) || ! is_object($postTypeVars['cap'])) {
            return '';
        }

        $caps = get_object_vars($postTypeVars['cap']);
        if (! isset($caps[$capName]) || ! is_string($caps[$capName]) || $caps[$capName] === '') {
            return '';
        }

        return $caps[$capName];
    }

    /**
     * @since 4.10.6
     *
     * @return ExpirationActionsModel|null
     */
    private function getExpirationActionsModel(): ?ExpirationActionsModel
    {
        $actionsModel = Container::getInstance()->get(ServicesAbstract::EXPIRATION_ACTIONS_MODEL);
        if (! $actionsModel instanceof ExpirationActionsModel) {
            return null;
        }

        return $actionsModel;
    }

    /**
     * @since 4.10.6
     *
     * @param array<int, mixed> $options
     * @param string[] $deniedValues
     * @return array<int, mixed>
     */
    private function removeSelectOptionValues(array $options, array $deniedValues): array
    {
        if ($deniedValues === []) {
            return $options;
        }

        $filtered = [];

        foreach ($options as $option) {
            if (! is_array($option) || ! isset($option['value'])) {
                $filtered[] = $option;
                continue;
            }

            if (in_array((string) $option['value'], $deniedValues, true)) {
                continue;
            }

            $filtered[] = $option;
        }

        return $filtered;
    }
}
