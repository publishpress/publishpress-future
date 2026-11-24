<?php

namespace PublishPress\Future;

use PublishPress\Future\Core\Plugin;

\defined('ABSPATH') || exit;

if (! function_exists(__NAMESPACE__ . '\\install')) {

    function install()
    {
        Plugin::onActivate();
    }
}
