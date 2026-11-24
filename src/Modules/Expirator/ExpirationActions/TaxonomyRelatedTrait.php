<?php

namespace PublishPress\Future\Modules\Expirator\ExpirationActions;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;

\defined('ABSPATH') || exit;

trait TaxonomyRelatedTrait
{
    public static function getTaxonomyLabel(string $postType): string
    {
        $container = Container::getInstance();
        $postTypeDefaultsModelFactory = $container->get(ServicesAbstract::POST_TYPE_DEFAULT_DATA_MODEL_FACTORY);
        $model = $postTypeDefaultsModelFactory->create($postType);

        $taxonomy = $model->getTaxonomy();
        $taxonomy = get_taxonomy($taxonomy);

        if (is_bool($taxonomy)) {
            return '';
        }

        return strtolower($taxonomy->labels->name);
    }
}
