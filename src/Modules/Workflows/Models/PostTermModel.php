<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use PublishPress\Future\Modules\Workflows\Interfaces\PostTermsModelInterface;

\defined('ABSPATH') || exit;

class PostTermModel implements PostTermsModelInterface
{
    public function getAllTerms(): array
    {
        $allTerms = [];

        $taxonomies = get_taxonomies(['public' => true], 'names');

        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms([
                'hide_empty' => false,
                'taxonomy' => $taxonomy,
            ]);

            if (is_wp_error($terms)) {
                continue;
            }

            $allTerms[$taxonomy] = $terms;
        }

        return $allTerms;
    }

    public function getAllTermsAsOptions(): array
    {
        $terms = $this->getAllTerms();

        $options = [];

        foreach ($terms as $taxonomy => $terms) {
            $taxonomyLabel = get_taxonomy($taxonomy)->label;

            foreach ($terms as $term) {
                $options[] = [
                    'label' => "$taxonomyLabel: $term->name",
                    'value' => "$taxonomy:$term->term_id",
                ];
            }
        }

        return $options;
    }
}
