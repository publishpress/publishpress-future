<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Models;

use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Framework\WordPress\Exceptions\NonexistentPostException;
use PublishPress\Future\Framework\WordPress\Exceptions\NonexistentTermException;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use WP_Post;

use function wp_update_post;

\defined('ABSPATH') || exit;


class PostModel
{
    /**
     * @var int
     */
    private $postId;
    /**
     * @var WP_Post
     */
    private $postInstance;

    /**
     * @var \Closure
     */
    protected $termModelFactory;

    /**
     * @var HookableInterface
     */
    protected $hooks;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        $post,
        $termModelFactory,
        HookableInterface $hooks,
        LoggerInterface $logger
    ) {
        if (is_object($post)) {
            $this->postInstance = $post;
            $this->postId = $post->ID;
        }

        if (is_numeric($post)) {
            $this->postId = (int) $post;
        }

        $this->termModelFactory = $termModelFactory;
        $this->hooks = $hooks;
        $this->logger = $logger;
    }

    /**
     * @param string $newPostStatus
     *
     * @return bool
     * @throws \PublishPress\Future\Framework\WordPress\Exceptions\NonexistentPostException
     */
    public function setPostStatus($newPostStatus)
    {
        if ($newPostStatus === 'publish') {
            return $this->publish(false);
        }

        $postData = [
            'post_status' => $newPostStatus,
        ];

        return $this->update($postData);
    }

    /**
     * @param bool $updateDateInFuture
     *
     * @return bool
     * @throws \PublishPress\Future\Framework\WordPress\Exceptions\NonexistentPostException
     */
    public function publish($updateDateInFuture = true)
    {
        if ($updateDateInFuture) {
            $postData = ['post_status' => 'publish'];
            $currentPostDate = get_post_field('post_date', $this->getPostId());

            if (strtotime($currentPostDate) > current_time('timestamp')) {
                $postData['post_date'] = current_time('mysql');
                $postData['post_date_gmt'] = current_time('mysql', true);
            }

            $this->update($postData);
        }

        return wp_publish_post($this->getPostId()) ? true : false;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function update($data)
    {
        $data = array_merge(
            ['ID' => $this->getPostId()],
            $data
        );

        return wp_update_post($data) > 0;
    }

    /**
     * @param string $metaKey
     * @param mixed $metaValue
     * @param bool $unique
     *
     * @return false|int
     */
    public function addMeta($metaKey, $metaValue = null, $unique = true)
    {
        return add_post_meta($this->getPostId(), $metaKey, $metaValue, $unique);
    }

    /**
     * @param string|array $metaKey
     * @param mixed $metaValue
     * @return void
     */
    public function updateMeta($metaKey, $metaValue = null)
    {
        if (! is_array($metaKey)) {
            $metaKey = [$metaKey => $metaValue];
        }

        $postId = $this->getPostId();

        foreach ($metaKey as $key => $value) {
            \update_post_meta(
                $postId,
                \sanitize_key($key),
                $value
            );
        }
    }

    /**
     * @param string|array $metaKey
     * @param mixed $metaValue
     * @return void
     */
    public function deleteMeta($metaKey, $metaValue = null)
    {
        if (! is_array($metaKey)) {
            $metaKey = [$metaKey];
        }

        $postId = $this->getPostId();

        foreach ($metaKey as $key) {
            \delete_post_meta(
                $postId,
                \sanitize_key($key),
                $metaValue
            );
        }
    }

    public function getMeta($metaKey, $single = false)
    {
        $postId = $this->getPostId();

        return get_post_meta($postId, $metaKey, $single);
    }

    public function metadataExists($metaKey)
    {
        $postId = $this->getPostId();

        return metadata_exists('post', $postId, $metaKey);
    }

    /**
     * @return bool
     * @throws \PublishPress\Future\Framework\WordPress\Exceptions\NonexistentPostException
     */
    public function postExists()
    {
        $instance = $this->getPostInstance();

        return is_object($instance);
    }

    /**
     * @return WP_Post
     * @throws \PublishPress\Future\Framework\WordPress\Exceptions\NonexistentPostException
     */
    protected function getPostInstance()
    {
        if (empty($this->postInstance)) {
            $this->postInstance = \get_post($this->getPostId());

            if (! is_object($this->postInstance) || is_wp_error($this->postInstance)) {
                throw new NonexistentPostException();
            }
        }

        return $this->postInstance;
    }

    public function getPostType()
    {
        return get_post_type($this->getPostId());
    }

    public function getTitle()
    {
        return get_the_title($this->getPostId());
    }

    public function getPostStatus()
    {
        return get_post_status($this->getPostId());
    }

    public function getPermalink()
    {
        return get_post_permalink($this->getPostId());
    }

    public function getPostEditLink()
    {
        return get_edit_post_link($this->getPostId());
    }

    public function getPostId(): int
    {
        return (int) $this->postId;
    }

    public function getTerms($taxonomy = 'post_tag', $args = [])
    {
        $terms = wp_get_post_terms($this->getPostId(), $taxonomy, $args);
        $termModelFactory = $this->termModelFactory;

        foreach ($terms as &$term) {
            $term = $termModelFactory($term);
        }

        return $terms;
    }

    public function getTermNames($taxonomy = 'post_tag', $args = [])
    {
        $terms = $this->getTerms($taxonomy, $args);

        foreach ($terms as &$term) {
            try {
                $term = $term->getName();
            } catch (NonexistentTermException $e) {
                $this->logger->error(
                    'Nonexistent term: {term} in {method}',
                    ['term' => $term, 'method' => __METHOD__]
                );

                continue;
            }
        }

        return $terms;
    }

    public function getTermIDs($taxonomy = 'post_tag', $args = [])
    {
        $terms = $this->getTerms($taxonomy, $args);

        foreach ($terms as &$term) {
            $term = $term->getTermID();
        }

        return $terms;
    }

    public function appendTerms($termIDs, $taxonomy)
    {
        return wp_set_object_terms($this->getPostId(), $termIDs, $taxonomy, true);
    }

    public function setTerms($termIDs, $taxonomy)
    {
        return wp_set_object_terms($this->getPostId(), $termIDs, $taxonomy, false);
    }

    public function delete(bool $force = true): bool
    {
        return wp_delete_post($this->getPostId(), $force) !== false;
    }

    public function trash()
    {
        return wp_trash_post($this->getPostId());
    }

    public function stick()
    {
        stick_post($this->getPostId());

        return true;
    }

    public function unstick()
    {
        unstick_post($this->getPostId());

        return true;
    }

    /**
     * @return string
     */
    public function getPostTypeSingularLabel()
    {
        $postTypeObj = get_post_type_object($this->getPostType());

        if (is_object($postTypeObj)) {
            return $postTypeObj->labels->singular_name;
        }

        return sprintf('[%s]', $this->getPostType());
    }
}
