<?php

namespace PublishPress\Future\Modules\Backup;

\defined('ABSPATH') || exit;

abstract class HooksAbstract
{
    public const ACTION_AFTER_IMPORT_SETTINGS = 'publishpressfuture_after_import_settings';
    public const FILTER_EXPORTED_SETTINGS = 'publishpressfuture_exported_settings';
}
