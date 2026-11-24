<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Settings;

\defined('ABSPATH') || exit;

abstract class HooksAbstract
{
    public const ACTION_DELETE_ALL_SETTINGS = 'publishpressfuture_delete_settings';
    public const FILTER_DEBUG_ENABLED = 'publishpressfuture_debug_enabled';
    public const ACTION_SAVE_TAB_PREFIX = 'publishpressfuture_save_tab_';
    public const FILTER_ALLOWED_TABS = 'publishpressfuture_allowed_tabs';
    public const FILTER_ALLOWED_SETTINGS_TABS = 'publishpressfuture_allowed_settings_tabs';
    public const FILTER_SETTINGS_TABS = 'publishpressfuture_settings_tabs';
    public const FILTER_SETTINGS_DEFAULT_TAB = 'publishpressfuture_settings_default_tab';
    public const FILTER_FUTURE_ACTIONS_TABS = 'publishpressfuture_future_actions_tabs';
    public const ACTION_LOAD_TAB = 'publishpressfuture_load_tab';
    public const FILTER_SHOW_PRO_BANNER = 'publishpressfuture_show_pro_banner';
    public const FILTER_SAVE_DEFAULTS_SETTINGS = 'publishpressfuture_save_defaults_settings';
    public const ACTION_SAVE_POST_TYPE_SETTINGS = 'publishpressfuture_saved_post_type_settings';
    public const ACTION_SAVE_ALL_POST_TYPES_SETTINGS = 'publishpressfuture_saved_all_post_types_settings';
    public const FILTER_SETTINGS_POST_TYPE = 'publishpress_future_settings_post_type';
    public const ACTION_SETTINGS_TAB_ADVANCED_BEFORE = 'publishpress_future/settings_tab_advanced_before';
    public const ACTION_FIX_DB_SCHEMA = 'publishpressfuture_fix_db_schema';
    public const FILTER_SCHEMA_IS_HEALTHY = 'publishpressfuture_schema_is_healthy';
    public const FILTER_SETTINGS_GENERAL = 'publishpressfuture_settings_general';
    public const FILTER_SETTINGS_NOTIFICATIONS = 'publishpressfuture_settings_notifications';
    public const FILTER_SETTINGS_DISPLAY = 'publishpressfuture_settings_display';
    public const FILTER_SETTINGS_ADMIN = 'publishpressfuture_settings_admin';
    public const FILTER_SETTINGS_ADVANCED = 'publishpressfuture_settings_advanced';
    public const FILTER_SETTINGS_POST_TYPE_DEFAULTS = 'publishpressfuture_settings_post_type_defaults';
    public const ACTION_SETTINGS_SET_POST_TYPE_DEFAULTS = 'publishpressfuture_settings_set_post_type_defaults';
    public const ACTION_SETTINGS_SET_GENERAL = 'publishpressfuture_settings_set_general';
    public const ACTION_SETTINGS_SET_NOTIFICATIONS = 'publishpressfuture_settings_set_notifications';
    public const ACTION_SETTINGS_SET_DISPLAY = 'publishpressfuture_settings_set_display';
    public const ACTION_SETTINGS_SET_ADMIN = 'publishpressfuture_settings_set_admin';
    public const ACTION_SETTINGS_SET_ADVANCED = 'publishpressfuture_settings_set_advanced';
}
