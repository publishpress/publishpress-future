<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

interface PostCacheInterface
{
    public function setup(): void;

    /**
     * Retrieves the cached posts, terms and permalinks for a given post ID.
     *
     * This method returns an array containing both before and after states of the post, terms and its permalink.
     *
     * @param int $postId The ID of the post to retrieve cached data for.
     *
     * @return array|null The cached data or null if the cache does not exist.
     */
    public function getCacheForPostId(int $postId): ?array;


    /**
     * Retrieves the added post terms ids for a specific taxonomy from cache.
     *
     * This method returns an array containing only the new terms IDs added to the post.
     *
     * @param int    $postId   The post ID.
     * @param string $taxonomy The taxonomy.
     *
     * @return array The added terms Ids or empty array.
     */
    public function getAddedTermsIds(int $postId, string $taxonomy): array;

    /**
     * Updates the postAfter entry in the cache for a given post ID.
     *
     * @param int      $postId The post ID.
     * @param \WP_Post $post   The fresh post object to store as postAfter.
     */
    public function setPostAfter(int $postId, \WP_Post $post): void;

    /**
     * Restores the postBefore entry in the cache for a given post ID.
     * Used to preserve the original pre-edit state when subsequent saves
     * (e.g. from ACF calling wp_update_post) would otherwise overwrite it.
     *
     * @param int      $postId The post ID.
     * @param \WP_Post $post   The post object to store as postBefore.
     */
    public function setPostBefore(int $postId, \WP_Post $post): void;
}
