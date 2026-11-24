<?php

namespace PublishPress\Future\Core\DI;

\defined('ABSPATH') || exit;

/**
 * Describes the interface of a container that exposes methods to read its entries.
 */
interface ContainerInterface extends \PublishPress\Psr\Container\ContainerInterface
{
    public function registerServices($services);

    /**
     * Resolve a factory service and invoke it with the given arguments.
     *
     * @since 4.11.0
     *
     * @param string $id Identifier of the factory service.
     * @param mixed ...$args Arguments passed to the factory.
     *
     * @return mixed Factory result.
     */
    public function build(string $id, ...$args);
}
