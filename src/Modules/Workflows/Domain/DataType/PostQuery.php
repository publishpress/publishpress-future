<?php

namespace PublishPress\Future\Modules\Workflows\Domain\DataType;

use PublishPress\Future\Modules\Workflows\Interfaces\DataTypeInterface;

\defined('ABSPATH') || exit;

class PostQuery implements DataTypeInterface
{
    public function getName(): string
    {
        return "postQuery";
    }

    public function getLabel(): string
    {
        return __("Post Query", "post-expirator");
    }

    public function getDescription(): string
    {
        return __("A query to select posts", "post-expirator");
    }
}
