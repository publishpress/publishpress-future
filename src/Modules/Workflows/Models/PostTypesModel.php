<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use PublishPress\Future\Modules\Workflows\Interfaces\PostTypesModelInterface;

\defined('ABSPATH') || exit;

class PostTypesModel implements PostTypesModelInterface
{
    public function getPostTypes(): array
    {
        return get_post_types([], "objects");
    }

    public function getPostTypesAsOptions(): array
    {
        $postTypes = $this->getPostTypes();

        $options = [];

        foreach ($postTypes as $postType) {
            $options[] = [
                'label' => $postType->label,
                'value' => $postType->name,
            ];
        }

        return $options;
    }
}
