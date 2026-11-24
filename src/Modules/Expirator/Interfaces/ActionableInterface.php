<?php

namespace PublishPress\Future\Modules\Expirator\Interfaces;

\defined('ABSPATH') || exit;

interface ActionableInterface
{
    /**
     * @return void
     */
    public function setAction($action);
}
