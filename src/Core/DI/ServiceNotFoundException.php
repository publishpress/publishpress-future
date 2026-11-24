<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Core\DI;

use InvalidArgumentException;
use PublishPress\Psr\Container\NotFoundExceptionInterface;

\defined('ABSPATH') || exit;

class ServiceNotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface
{
    public function __construct($message = "", $code = 0, $previous = null)
    {
        $message = "No entry or class found in the container for service '$message'";

        parent::__construct($message, $code, $previous);
    }
}
