<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Core\DI;

\defined('ABSPATH') || exit;

/**
 * @deprecated 3.1.1
 */
class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @var \Closure[]
     */
    protected $factories;

    /**
     * @param \Closure[] $factories
     */
    public function __construct($factories)
    {
        $this->factories = $factories;
    }

    /**
     * @inheritDoc
     */
    public function getFactories()
    {
        return $this->factories;
    }
}
