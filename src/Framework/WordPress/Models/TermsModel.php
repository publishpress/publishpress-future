<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Models;

\defined('ABSPATH') || exit;

class TermsModel
{
    public function getTermNamesByIdAsString($termIds, $taxonomy)
    {
        $termNames = [];
        foreach ($termIds as $termId) {
            $term = get_term($termId, $taxonomy);
            if ($term instanceof \WP_Term) {
                $termNames[] = $term->name;
            }
        }

        return implode(', ', $termNames);
    }
}
