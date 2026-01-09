<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Core;

defined('ABSPATH') or die('Direct access not allowed.');

abstract class HooksAbstract
{
    public const ACTION_INIT = 'init';

    public const ACTION_ADMIN_INIT = 'admin_init';

    public const ACTION_ADMIN_MENU = 'admin_menu';

    public const ACTION_ADMIN_NOTICES = 'admin_notices';

    public const FILTER_ADMIN_TITLE = 'admin_title';

    public const ACTION_INIT_PLUGIN = 'publishpressfuture_init_plugin';

    public const ACTION_AFTER_INIT_PLUGIN = 'publishpressfuture_after_init_plugin';

    public const ACTION_ACTIVATE_PLUGIN = 'publishpressfuture_activate_plugin';

    public const ACTION_DEACTIVATE_PLUGIN = 'publishpressfuture_deactivate_plugin';

    public const ACTION_ADMIN_ENQUEUE_SCRIPTS = 'admin_enqueue_scripts';

    public const ACTION_INSERT_POST = 'wp_insert_post';

    public const ACTION_PURGE_PLUGIN_CACHE = 'publishpressfuture_purge_plugin_cache';

    public const ACTION_BULK_EDIT_CUSTOM_BOX = 'bulk_edit_custom_box';

    public const ACTION_QUICK_EDIT_CUSTOM_BOX = 'quick_edit_custom_box';

    public const ACTION_SAVE_POST = 'save_post';

    public const ACTION_UPDATED_POST_META = 'updated_post_meta';

    public const ACTION_ADDED_POST_META = 'added_post_meta';

    public const ACTION_ADD_META_BOXES = 'add_meta_boxes';

    public const ACTION_ADMIN_PRINT_SCRIPTS_EDIT = 'admin_print_scripts-edit.php';

    public const ACTION_UPGRADE_PLUGIN = 'publishpressfuture_upgrade_plugin';

    public const ACTION_LOAD_POST_PHP = 'load-post.php';

    public const ACTION_LOAD_POST_NEW_PHP = 'load-post-new.php';

    public const ACTION_REST_API_INIT = 'rest_api_init';

    public const ACTION_ENQUEUE_BLOCK_EDITOR_ASSETS = 'enqueue_block_editor_assets';

    public const ACTION_PRE_POST_UPDATE = 'pre_post_update';

    public const ACTION_POST_UPDATED = 'post_updated';

    public const ACTION_TRANSITION_POST_STATUS = 'transition_post_status';

    public const ACTION_SET_OBJECT_TERMS = 'set_object_terms';

    public const ACTION_WP_INSERT_POST_DATA = 'wp_insert_post_data';

    public const ACTION_ADMIN_FOOTER = 'admin_footer';

    public const ACTION_MANAGE_POSTS_CUSTOM_COLUMN = 'manage_posts_custom_column';

    public const ACTION_MANAGE_PAGES_CUSTOM_COLUMN = 'manage_pages_custom_column';

    public const ACTION_POSTS_ORDER_BY = 'posts_orderby';

    public const FILTER_BULK_ACTIONS_POST_EDIT = 'bulk_actions-edit-%s';

    public const FILTER_MANAGE_POSTS_COLUMNS = 'manage_posts_columns';

    public const FILTER_MANAGE_PAGES_COLUMNS = 'manage_pages_columns';

    public const FILTER_POSTS_JOIN = 'posts_join';

    public const FILTER_THE_CONTENT = 'the_content';

    public const FILTER_THE_TITLE = 'the_title';

    public const FILTER_POST_ROW_ACTIONS = 'post_row_actions';

    public const FILTER_POST_UPDATED_MESSAGES = 'post_updated_messages';

    public const FILTER_BULK_POST_UPDATED_MESSAGES = 'bulk_post_updated_messages';

    public const FILTER_MODULES_LIST = 'publishpressfuture_list_modules';

    public const FILTER_PLUGIN_ACTION_LINKS = 'plugin_action_links';

    public const FILTER_PLUGIN_ROW_META = 'plugin_row_meta';

    public const FILTER_MIGRATIONS = 'publishpressfuture_migrations';

    public const FILTER_REMOVABLE_QUERY_ARGS = 'removable_query_args';

    /**
     * @deprecated 4.1.0 use ACTION_ADMIN_ENQUEUE_SCRIPTS instead
     */
    public const ACTION_ADMIN_ENQUEUE_SCRIPT = 'admin_enqueue_scripts';

    /**
     * @deprecated 3.1.5 use ACTION_ADD_META_BOXES instead
     */
    public const ACTION_ADD_META_BOX = self::ACTION_ADD_META_BOXES;

    /**
     * @since 4.9.3
     */
    public const ACTION_AFTER_INSERT_POST = 'wp_after_insert_post';
}
