<?php

defined('ABSPATH') || exit;

/**
 * Autoloads the classes.
 *
 * @deprecated 2.8.0
 */
function postexpirator_autoload($class)
{
    $namespaces = ['PostExpirator'];
    foreach ($namespaces as $namespace) {
        if (substr($class, 0, strlen($namespace)) === $namespace) {
            $class = str_replace('_', '', strstr($class, '_'));

            $filename = POSTEXPIRATOR_LEGACYDIR . '/classes/' . sprintf('%s.class.php', $class);
            if (is_readable($filename)) {
                require_once $filename;

                return true;
            }
        }
    }

    return false;
}

spl_autoload_register('postexpirator_autoload');
