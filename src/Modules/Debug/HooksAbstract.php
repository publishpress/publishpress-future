<?php

namespace PublishPress\Future\Modules\Debug;

defined('ABSPATH') or die('Direct access not allowed.');

abstract class HooksAbstract
{
    public const ACTION_DEBUG_LOG = 'publishpressfuture_debug_log';

    public const ACTION_AFTER_DEBUG_LOG_SETTING = 'publishpressfuture_after_debug_log_setting';

    public const ACTION_MIGRATE_DEBUG_LOG_REQUEST_ID = 'publishpress_future/v04905_debug_log_request_id';

    public const ACTION_MIGRATE_DEBUG_LOG_TIMESTAMP_MS = 'publishpress_future/v04906_debug_log_timestamp_ms';
}
