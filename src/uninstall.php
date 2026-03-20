<?php

namespace PublishPress\Future;

use PublishPress\Future\Core\Plugin;

defined('ABSPATH') or die('No direct script access allowed.');

if (! function_exists(__NAMESPACE__ . '\\uninstall')) {

    function uninstall()
    {
        loadPluginDependencies();
        Plugin::onDeactivate();
    }
}
