<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use PublishPress\Future\Modules\Workflows\Interfaces\PostAuthorsModelInterface;

\defined('ABSPATH') || exit;

class PostAuthorsModel implements PostAuthorsModelInterface
{
    public function getAuthors(): array
    {
        $authors = get_users([
            'capability' => ['edit_posts'],
            'orderby' => 'display_name',
            'order' => 'ASC',
        ]);

        return $authors;
    }

    public function getAuthorsAsOptions(): array
    {
        $authors = $this->getAuthors();

        foreach ($authors as $author) {
            $options[] = [
                'label' => $author->display_name,
                'value' => $author->ID,
            ];
        }

        return $options;
    }
}
