<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use PublishPress\Future\Modules\Workflows\Interfaces\PostStatusesModelInterface;

\defined('ABSPATH') || exit;

class PostStatusesModel implements PostStatusesModelInterface
{
    public function getPostStatuses(): array
    {
        return get_post_stati([], 'objects');
    }

    public function getPostStatusesAsOptions(): array
    {
        $postStatuses = $this->getPostStatuses();

        $options = [];
        $statusesLabels = [];

        // If there are duplicated statuses show the name after the label.
        foreach ($postStatuses as $postStatus) {
            $options[] = [
                'label' => $postStatus->label,
                'value' => $postStatus->name,
            ];

            $statusesLabels[] = $postStatus->label;
        }

        // Count the number of times each status appears in the array.
        $statusCount = array_count_values($statusesLabels);

        // If there are duplicated statuses show the name after the label.
        foreach ($options as $key => $option) {
            if ($statusCount[$option['label']] > 1) {
                $options[$key]['label'] .= ' (' . $option['value'] . ')';
            }
        }

        return $options;
    }
}
