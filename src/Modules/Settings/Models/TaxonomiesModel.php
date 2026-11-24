<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Settings\Models;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Expirator\Models\PostTypesModel;

\defined('ABSPATH') || exit;

class TaxonomiesModel
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    private function getPostTypes()
    {
        $container = Container::getInstance();
        $postTypesModel = new PostTypesModel($container);

        return $postTypesModel->getPostTypes();
    }

    public function getTaxonomiesByPostType($hierarchical = true)
    {
        $postTypes = $this->getPostTypes();

        $taxonomiesByPostType = [];

        foreach ($postTypes as $postType) {
            $taxonomies = get_object_taxonomies($postType, 'object');

            if ($hierarchical) {
                $taxonomies = wp_filter_object_list($taxonomies, ['hierarchical' => true]);
            }

            $taxonomiesByPostType[$postType] = $taxonomies;
        }

        return $taxonomiesByPostType;
    }

    public function getTermIdByName($taxonomy, $termName)
    {
        $term = get_term_by('name', $termName, $taxonomy);

        if (!$term) {
            return 0;
        }

        return $term->term_id;
    }

    /**
     * Creates a term and returns its ID when the current user may edit that taxonomy.
     *
     * @param string      $taxonomy Taxonomy name.
     * @param string      $termName Term name.
     * @param string|null $postType Post type the taxonomy must belong to. Empty skips that check.
     *
     * @return int Term ID, or 0 when creation is blocked or fails.
     *
     * @since 4.10.6
     */
    public function createTermAndReturnId($taxonomy, $termName, $postType = null)
    {
        $taxonomyObject = $this->getTaxonomyForPostType($taxonomy, $postType);

        if (! $taxonomyObject || ! current_user_can($taxonomyObject->cap->edit_terms)) {
            return 0;
        }

        $term = wp_insert_term($termName, $taxonomy);

        if (is_wp_error($term)) {
            return 0;
        }

        return $term['term_id'];
    }

    /**
     * Normalizes term IDs and names, creating missing terms only when allowed.
     *
     * Users who cannot edit the taxonomy still resolve names of terms that already exist.
     *
     * @param string      $taxonomy Taxonomy name.
     * @param mixed       $terms    Term IDs or names.
     * @param string|null $postType Post type the taxonomy must belong to. Empty skips that check.
     *
     * @return array<int, int>
     *
     * @since 4.10.6
     */
    public function normalizeTermsCreatingIfNecessary($taxonomy, $terms, $postType = null)
    {
        $taxonomyObject = $this->getTaxonomyForPostType($taxonomy, $postType);

        if (! $taxonomyObject || ! is_array($terms)) {
            return [];
        }

        $numericIds = [];
        $names = [];

        foreach ($terms as $term) {
            if (is_numeric($term)) {
                $numericIds[] = intval($term);
                continue;
            }

            $names[] = $term;
        }

        if (empty($names)) {
            return $numericIds;
        }

        $names = array_map('sanitize_text_field', $names);

        if (! current_user_can($taxonomyObject->cap->edit_terms)) {
            $resolvedIds = [];

            foreach ($names as $name) {
                $existingTerm = get_term_by('name', $name, $taxonomy);

                if ($existingTerm instanceof \WP_Term) {
                    $resolvedIds[] = (int) $existingTerm->term_id;
                }
            }

            $this->logger->debug(
                sprintf(
                    'Current user cannot create terms for taxonomy %1$s. Resolved existing terms by name.',
                    $taxonomy
                )
            );

            return array_values(array_unique(array_map('intval', array_merge($numericIds, $resolvedIds))));
        }

        $createdIds = array_map(function ($newTerm) use ($taxonomy) {
            $insertedTerm = wp_insert_term($newTerm, $taxonomy);

            if (is_wp_error($insertedTerm) && $insertedTerm->get_error_code() === 'term_exists') {
                return $insertedTerm->get_error_data('term_exists');
            }

            if (is_wp_error($insertedTerm)) {
                $this->logger->error(
                    sprintf(
                        'Error creating term for taxonomy %1$s: %2$s',
                        $taxonomy,
                        $insertedTerm->get_error_message()
                    )
                );

                return 0;
            }

            return $insertedTerm['term_id'];
        }, $names);

        return array_map('intval', array_merge($numericIds, $createdIds));
    }

    /**
     * Returns the taxonomy when it is registered for the post type.
     *
     * @param string      $taxonomy Taxonomy name.
     * @param string|null $postType Post type. Empty skips the object check.
     *
     * @return \WP_Taxonomy|null
     *
     * @since 4.10.6
     */
    private function getTaxonomyForPostType($taxonomy, $postType = null)
    {
        $taxonomyObject = get_taxonomy($taxonomy);

        if (! $taxonomyObject) {
            return null;
        }

        if (! empty($postType) && ! is_object_in_taxonomy($postType, $taxonomy)) {
            return null;
        }

        return $taxonomyObject;
    }
}
