<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Caches;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\HooksAbstract;
use PublishPress\Future\Modules\Workflows\Interfaces\PostCacheInterface;

use function get_permalink;

class PostCache implements PostCacheInterface
{
    private array $cache = [];

    private HookableInterface $hooks;

    public function __construct(HookableInterface $hooks)
    {
        $this->hooks = $hooks;
    }

    public function setup(): void
    {
        $this->hooks->addAction(HooksAbstract::ACTION_PRE_POST_UPDATE, [$this, 'storeCacheBeforeUpdate'], 15);
        $this->hooks->addAction(HooksAbstract::ACTION_POST_UPDATED, [$this, 'storeCacheAfterUpdate'], 15, 3);

        $this->hooks->addFilter(HooksAbstract::ACTION_WP_INSERT_POST_DATA, [$this, 'storeCacheBeforeInsert'], 9999);
        $this->hooks->addAction(HooksAbstract::ACTION_SAVE_POST, [$this, 'storeCacheAfterInsert'], 10, 2);

        $this->hooks->addAction(HooksAbstract::ACTION_TRANSITION_POST_STATUS, [$this, 'storeCacheAfterTransition'], 15, 3);

        $this->hooks->addAction(HooksAbstract::ACTION_SET_OBJECT_TERMS, [$this, 'storeTermsCache'], 10, 6);
    }

    /**
     * Stores the original permalink before update.
     * This ensures we can access the old URL since post resolver
     * would only return the updated permalink.
     *
     * @param int $postId The post ID.
     *
     * @return void
     */
    public function storeCacheBeforeUpdate(int $postId): void
    {
        $this->ensureCacheExists($postId);

        $this->cache[$postId]['permalinkBefore'] = $this->getPostPermalink($postId);
    }

    /**
     * Stores the cached post after update.
     *
     * This method captures the post state after it has been updated in the database.
     * It ensures that the cache exists for the given post ID before storing the data.
     *
     * @param int       $postId    The post ID.
     * @param \WP_Post  $postAfter The post object after update.
     * @param \WP_Post  $postBefore The post object before update.
     *
     * @return void
     */
    public function storeCacheAfterUpdate(int $postId, \WP_Post $postAfter, \WP_Post $postBefore): void
    {
        $this->ensureCacheExists($postId);

        $this->cache[$postId]['postAfter'] = $postAfter;
        $this->cache[$postId]['postBefore'] = $postBefore;

        $this->cache[$postId]['permalinkAfter'] = $this->getPostPermalink($postId);
    }

    /**
     * Stores cache data before a post is inserted.
     *
     * This method captures the post state before it is saved to the database.
     *
     * @param array $data                An array of slashed, sanitized, and processed post data.
     *
     * @return array The data array.
     */
    public function storeCacheBeforeInsert(array $data): array
    {
        // If no ID is set, we are inserting a new post and temporarily store the data in the cache[0] index.
        $postId = $data['ID'] ?? 0;

        $this->ensureCacheExists($postId);

        $postData = (object) $data;
        $postBefore = new \WP_Post($postData);
        $postBefore->post_status = 'new';

        $this->cache[$postId]['postBefore'] = $postBefore;
        $this->cache[$postId]['permalinkBefore'] = $this->getPostPermalink($postId);

        return $data;
    }

    /**
     * Stores cache data after a post is inserted or updated.
     *
     * This method captures the post state after it has been saved to the database.
     *
     * @param int       $postId    The post ID.
     * @param \WP_Post  $post      The post object after save.
     *
     * @return void
     */
    public function storeCacheAfterInsert(int $postId, \WP_Post $post): void
    {
        $this->ensureCacheExists($postId);

        $this->replaceCache0IfExist($postId);

        $this->cache[$postId]['postAfter'] = $post;
        $this->cache[$postId]['permalinkAfter'] = $this->getPostPermalink($postId);
    }

    /**
     * Stores cache data after a post status transition.
     *
     * This method captures the post state after it has been transitioned to a new status.
     *
     * @param string    $newStatus The new post status.
     * @param string    $oldStatus The old post status.
     * @param \WP_Post  $post      The post object after transition.
     *
     * @return void
     */
    public function storeCacheAfterTransition(string $newStatus, string $oldStatus, \WP_Post $post): void
    {
        $this->ensureCacheExists($post->ID);

        $this->replaceCache0IfExist($post->ID);

        $oldPost = clone $post;
        $oldPost->post_status = $oldStatus;


        $this->cache[$post->ID]['postBefore'] = $oldPost;
        // Since this method is called during a status transition rather than a full post update,
        // we don't have access to the previous permalink from here.
        $this->cache[$post->ID]['permalinkBefore'] = $this->getPostPermalink($post->ID);
    }

    /**
     * Store taxonomy term before and after cache for posts.
     *
     * @param int    $objectId  The object ID.
     * @param array  $terms     The terms.
     * @param array  $ttIds     The term taxonomy IDs after update.
     * @param string $taxonomy  The taxonomy.
     * @param bool   $append    Whether to append terms.
     * @param array  $oldTtIds  The term taxonomy IDs before update.
     *
     * @return void
     */
    public function storeTermsCache($objectId, $terms, $ttIds, $taxonomy, $append, $oldTtIds): void
    {
        $post = get_post($objectId);

        if (!$post) {
            return;
        }

        $this->ensureCacheExists($objectId);

        $existingTermsBefore = $this->cache[$objectId]['termsBefore'] ?? [];
        $existingTermsAfter = $this->cache[$objectId]['termsAfter'] ?? [];

        $allTaxonomies = get_object_taxonomies($post->post_type, 'names');

        foreach ($allTaxonomies as $currentTaxonomy) {
            if ($currentTaxonomy === $taxonomy) {
                // Handle before state
                $beforeTaxonomyTermsData = [];
                if (!empty($oldTtIds)) {
                    $termsBefore = get_terms([
                        'include' => $oldTtIds,
                        'taxonomy' => $taxonomy,
                        'hide_empty' => false
                    ]);
                    if (!is_wp_error($termsBefore)) {
                        $beforeTaxonomyTermsData = $termsBefore;
                    }
                }
                $existingTermsBefore[$taxonomy] = $beforeTaxonomyTermsData;

                // Handle after state
                $afterTaxonomyTermsData = [];
                if (!empty($ttIds)) {
                    $termsAfter = get_terms([
                        'include' => $ttIds,
                        'taxonomy' => $taxonomy,
                        'hide_empty' => false
                    ]);
                    if (!is_wp_error($termsAfter)) {
                        $afterTaxonomyTermsData = $termsAfter;
                    }
                }
                $existingTermsAfter[$taxonomy] = $afterTaxonomyTermsData;
            } else {
                if (!isset($existingTermsBefore[$currentTaxonomy])) {
                    $currentTerms = wp_get_post_terms($objectId, $currentTaxonomy);
                    if (!is_wp_error($currentTerms)) {
                        $existingTermsBefore[$currentTaxonomy] = $currentTerms;
                        $existingTermsAfter[$currentTaxonomy] = $currentTerms;
                    } else {
                        $existingTermsBefore[$currentTaxonomy] = [];
                        $existingTermsAfter[$currentTaxonomy] = [];
                    }
                }
            }
        }

        // Store cache data
        $this->cache[$objectId]['termsBefore'] = $existingTermsBefore;
        $this->cache[$objectId]['termsAfter'] = $existingTermsAfter;
    }
    /**
     * Get added terms ids for a specific taxonomy.
     *
     * @param int    $postId   The post ID.
     * @param string $taxonomy The taxonomy.
     *
     * @return array The added terms Ids.
     */
    public function getAddedTermsIds(int $postId, string $taxonomy): array
    {
        $cache = $this->getCacheForPostId($postId);

        if (!$cache) {
            return [];
        }

        $beforeIds = [];
        if (!empty($cache['termsBefore'][$taxonomy])) {
            $beforeIds = array_map(function ($term) {
                return $term->term_id;
            }, $cache['termsBefore'][$taxonomy]);
        }

        $afterIds = [];
        if (!empty($cache['termsAfter'][$taxonomy])) {
            $afterIds = array_map(function ($term) {
                return $term->term_id;
            }, $cache['termsAfter'][$taxonomy]);
        }

        $addedIds = array_diff($afterIds, $beforeIds);

        return $addedIds;
    }

    /**
     * Retrieves the cached posts, terms and permalinks for a given post ID.
     *
     * This method returns an array containing both before and after states of the post, terms and its permalink.
     *
     * @param int $postId The ID of the post to retrieve cached data for.
     *
     * @return array|null The cached data or null if the cache does not exist.
     */
    public function getCacheForPostId(int $postId): ?array
    {
        $this->ensureCacheExists($postId);

        return $this->cache[$postId];
    }

    private function ensureCacheExists(int $postId): void
    {
        if (!isset($this->cache[$postId])) {
            $this->cache[$postId] = [
                'postBefore' => null,
                'postAfter' => null,
                'permalinkBefore' => '',
                'permalinkAfter' => '',
                'termsBefore' => [],
                'termsAfter' => [],
            ];
        }
    }

    private function getPostPermalink(int $postId): string
    {
        $permalink = get_permalink($postId);

        if (! $permalink) {
            return '';
        }

        return $permalink;
    }

    /**
     * Move the data from the cache[0] index to the cache[$postId] index.
     */
    private function replaceCache0IfExist(int $postId): void
    {
        if (isset($this->cache[0])) {
            $this->cache[$postId] = $this->cache[0];
            unset($this->cache[0]);
        }
    }
}
