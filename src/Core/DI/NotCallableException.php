<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Core\DI;

use InvalidArgumentException;
use PublishPress\Psr\Container\ContainerExceptionInterface;

\defined('ABSPATH') || exit;

/**
 * Thrown when a resolved container entry is not callable.
 *
 * @since 4.11.0
 */
class NotCallableException extends InvalidArgumentException implements ContainerExceptionInterface
{
    public function __construct($message = "", $code = 0, $previous = null)
    {
        $message = "Service '$message' is not callable";

        parent::__construct($message, $code, $previous);
    }
}
