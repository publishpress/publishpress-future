<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator;

use PublishPress\Future\Core\HooksAbstract as CoreHooksAbstract;

defined('ABSPATH') or die('Direct access not allowed.');

abstract class HooksAbstract
{
    public const ACTION_INIT = CoreHooksAbstract::ACTION_INIT;

    public const ACTION_ADMIN_INIT = CoreHooksAbstract::ACTION_ADMIN_INIT;

    public const ACTION_ADMIN_MENU = CoreHooksAbstract::ACTION_ADMIN_MENU;

    public const ACTION_ADMIN_NOTICES = CoreHooksAbstract::ACTION_ADMIN_NOTICES;

    public const ACTION_REST_API_INIT = CoreHooksAbstract::ACTION_REST_API_INIT;

    public const ACTION_INSERT_POST = CoreHooksAbstract::ACTION_INSERT_POST;

    public const ACTION_MANAGE_POSTS_CUSTOM_COLUMN = CoreHooksAbstract::ACTION_MANAGE_POSTS_CUSTOM_COLUMN;

    public const ACTION_MANAGE_PAGES_CUSTOM_COLUMN = CoreHooksAbstract::ACTION_MANAGE_PAGES_CUSTOM_COLUMN;

    public const ACTION_POSTS_ORDER_BY = CoreHooksAbstract::ACTION_POSTS_ORDER_BY;

    public const ACTION_POST_UPDATED = CoreHooksAbstract::ACTION_POST_UPDATED;

    public const FILTER_THE_CONTENT = CoreHooksAbstract::FILTER_THE_CONTENT;

    public const FILTER_BULK_ACTIONS_POST_EDIT = CoreHooksAbstract::FILTER_BULK_ACTIONS_POST_EDIT;

    public const FILTER_MANAGE_POSTS_COLUMNS = CoreHooksAbstract::FILTER_MANAGE_POSTS_COLUMNS;

    public const FILTER_MANAGE_PAGES_COLUMNS = CoreHooksAbstract::FILTER_MANAGE_PAGES_COLUMNS;

    public const FILTER_POSTS_JOIN = CoreHooksAbstract::FILTER_POSTS_JOIN;

    public const ACTION_LEGACY_SCHEDULE = 'postexpirator_schedule';

    public const ACTION_LEGACY_UNSCHEDULE = 'postexpirator_unschedule';

    public const ACTION_LEGACY_EXPIRE_POST1 = 'postExpiratorExpire';

    public const ACTION_LEGACY_EXPIRE_POST2 = 'publishpressfuture_expire';

    public const ACTION_LEGACY_DELETE = 'expirationdate_delete';

    public const ACTION_LEGACY_MULTISITE_DELETE_PREFIX = 'expirationdate_delete_';

    public const FILTER_LEGACY_CUSTOM_EXPIRATION_TYPE = 'postexpirator_custom_posttype_expire';

    public const ACTION_SCHEDULE_POST_EXPIRATION = 'publishpressfuture_schedule_expiration';

    public const ACTION_UNSCHEDULE_POST_EXPIRATION = 'publishpressfuture_unschedule_expiration';

    public const ACTION_RUN_WORKFLOW = 'publishpressfuture_run_workflow';

    public const ACTION_LEGACY_RUN_WORKFLOW = 'publishpress_future/run_workflow';

    public const ACTION_POST_EXPIRED = 'publishpressfuture_post_expired';

    public const ACTION_MIGRATE_REPLACE_FOOTER_PLACEHOLDERS = 'publishpress_future/v30000_replace_footer_placeholders';

    public const ACTION_MIGRATE_WPCRON_EXPIRATIONS = 'publishpress_future/v30000_migrate_wpcron_expirations';

    public const ACTION_MIGRATE_CREATE_ACTION_ARGS_SCHEMA = 'publishpress_future/v30000_create_actions_args_schema';

    public const ACTION_MIGRATE_RESTORE_POST_META = 'publishpress_future/v30001_restore_post_meta';

    public const ACTION_MIGRATE_ARGS_LENGTH = 'publishpress_future/V30104ArgsColumnLength';

    public const ACTION_SCHEDULER_DELETED_ACTION = 'action_scheduler_deleted_action';

    public const ACTION_SCHEDULER_CANCELED_ACTION = 'action_scheduler_canceled_action';

    public const ACTION_SCHEDULER_AFTER_EXECUTE = 'action_scheduler_after_execute';

    public const ACTION_SCHEDULER_FAILED_EXECUTION = 'action_scheduler_failed_execution';

    public const ACTION_SYNC_SCHEDULER_WITH_POST_META = 'admin_action_sync_scheduler_with_post_meta';

    public const FILTER_CUSTOM_EXPIRATION_TYPE = 'publishpressfuture_custom_expiration_type';

    public const FILTER_LEGACY_TEMPLATE_PARAMS = 'publishpressfuture_legacy_template_params';

    public const FILTER_LEGACY_TEMPLATE_FILE = 'publishpressfuture_legacy_template_file';

    public const FILTER_EXPIRED_EMAIL_SUBJECT = 'publishpressfuture_expired_email_subject';

    public const FILTER_EXPIRED_EMAIL_BODY = 'publishpressfuture_expired_email_body';

    public const FILTER_EXPIRED_EMAIL_ADDRESSES = 'publishpressfuture_expired_email_addresses';

    public const FILTER_EXPIRED_EMAIL_HEADERS = 'publishpressfuture_expired_email_headers';

    public const FILTER_EXPIRED_EMAIL_ATTACHMENTS = 'publishpressfuture_expired_email_attachments';

    public const FILTER_EXPIRATION_ACTIONS = 'publishpressfuture_expiration_actions';

    public const FILTER_EXPIRATION_ACTION_FACTORY = 'publishpressfuture_expiration_action_factory';

    public const FILTER_ACTION_SCHEDULER_LIST_COLUMN_HOOK = 'publishpressfuture_action_scheduler_column_hook';

    public const FILTER_CONTENT_FOOTER = 'publishpress_future/content_footer';

    public const FILTER_ACTION_BASE_DATE_STRING = 'publishpress_future/action_base_date_string';

    public const FILTER_ACTION_META_KEY = 'publishpressfuture_action_meta_key';

    public const FILTER_SUPPORTED_POST_TYPES = 'publishpressfuture_supported_post_types';

    public const FILTER_UNSET_POST_TYPES_DEPRECATED = 'postexpirator_unset_post_types';

    public const FILTER_HIDE_METABOX = 'publishpressfuture_hide_metabox';

    public const FILTER_HIDDEN_METABOX_FIELDS = 'publishpressfuture_hidden_metabox_fields';

    public const FILTER_EXPIRATION_NEW_STATUS = 'publishpressfuture_expiration_new_status';

    public const FILTER_EXPIRATION_STATUSES = 'publishpressfuture_expiration_statuses';

    public const FILTER_PREPARE_POST_EXPIRATION_OPTS = 'publishpressfuture_prepare_post_expiration_opts';

    public const FILTER_ACTION_SCHEDULER_ADMIN_NOTICE = 'action_scheduler_admin_notice_html';

    public const FILTER_EXPIRATION_DATA_AS_ARRAY = 'publishpressfuture_expiration_data_as_array';

    public const FILTER_DISPLAY_BULK_ACTION_SYNC = 'publishpressfuture_display_bulk_action_sync';

    public static function getActionLegacyMultisiteDelete($blogId)
    {
        return self::ACTION_LEGACY_MULTISITE_DELETE_PREFIX . $blogId;
    }
}
