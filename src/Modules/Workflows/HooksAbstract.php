<?php

namespace PublishPress\Future\Modules\Workflows;

abstract class HooksAbstract
{
    public const FILTER_IS_PRO = 'publishpressfuture_is_pro';

    public const ACTION_WORKFLOW_ENGINE_LOAD = 'publishpressfuture_workflow_engine_load';

    public const ACTION_WORKFLOW_ENGINE_START = 'publishpressfuture_workflow_engine_start';

    public const ACTION_SAVE_POST = 'save_post';

    public const ACTION_PRE_POST_UPDATE = 'pre_post_update';

    public const ACTION_POST_UPDATED = 'post_updated';

    /**
     * @since 4.6.0
     */
    public const ACTION_TRANSITION_POST_STATUS = 'transition_post_status';

    /**
     * @since 4.9.0
     */
    public const ACTION_SET_OBJECT_TERMS = 'set_object_terms';

    public const ACTION_INIT = 'init';

    public const ACTION_ADMIN_INIT = 'admin_init';

    /**
     * @since 4.6.0
     */
    public const ACTION_WP_INSERT_POST_DATA = 'wp_insert_post_data';

    public const ACTION_TRIGGER_FIRED = 'publishpressfuture_workflow_trigger_fired_';

    /**
     * @deprecated 4.3.2 Use ACTION_EXECUTE_STEP instead.
     */
    public const ACTION_EXECUTE_NODE = 'publishpressfuture_workflow_execute_node';

    /**
     * @since 4.3.2
     */
    public const ACTION_EXECUTE_STEP = 'publishpressfuture_workflow_execute_node';

    /**
     * @deprecated 4.3.2 Use ACTION_SCHEDULED_STEP_EXECUTE instead.
     */
    public const ACTION_ASYNC_EXECUTE_NODE = 'publishpressfuture_workflow_async_execute_node';

    /**
     * We are just renaming the constant to be more intuitive. The value we are using is the same as the old one
     * to avoid breaking async actions scheduled by the old constant.
     *
     * @since 4.3.2
     * @deprecated 4.6.0 Use ACTION_SCHEDULED_STEP_EXECUTE instead.
     */
    public const ACTION_ASYNC_EXECUTE_STEP = 'publishpressfuture_workflow_async_execute_node';

    /**
     * Represents steps that are scheduled to run at a specific time.
     * Uses the same hook name as ACTION_ASYNC_EXECUTE_STEP for backward compatibility.
     *
     * @since 4.6.0
     */
    public const ACTION_SCHEDULED_STEP_EXECUTE = 'publishpressfuture_workflow_async_execute_node';

    /**
     * @deprecated 4.3.2 Use ACTION_UNSCHEDULE_RECURRING_STEP_ACTION instead.
     */
    public const ACTION_UNSCHEDULE_RECURRING_NODE_ACTION =
        'publishpressfuture_workflow_unschedule_recurring_node_action';

    /**
     * @since 4.3.2
     */
    public const ACTION_UNSCHEDULE_RECURRING_STEP_ACTION =
        'publishpressfuture_workflow_unschedule_recurring_step_action';

    public const ACTION_LEGACY_ACTION = 'publishpressfuture_legacy_action';

    public const ACTION_RENDER_WORKFLOW_EDITOR_PAGE = 'publishpressfuture_render_workflow_editor_page';

    public const ACTION_MANUALLY_TRIGGERED_WORKFLOW = 'publishpressfuture_manually_triggered_workflow';

    public const ACTION_UPDATE_WORKFLOW_STATUS = 'publishpressfuture_update_workflow_status';

    public const ACTION_MIGRATE_WORKFLOW_SCHEDULED_STEPS_SCHEMA =
        'publishpressfuture_migrate_workflow_scheduled_steps_schema';

    public const ACTION_MIGRATE_V040500_ON_SCHEDULED_STEPS =
        'publishpressfuture_migrate_post_id_on_scheduled_steps';

    public const ACTION_MIGRATE_REPETITION_NUMBER_ON_SCHEDULED_STEPS =
        'publishpressfuture_migrate_repetition_number_on_scheduled_steps';

    public const ACTION_CLEANUP_ORPHAN_WORKFLOW_ARGS = 'publishpressfuture_cleanup_orphan_workflow_args';

    public const ACTION_CLEANUP_FINISHED_SCHEDULED_STEPS = 'publishpressfuture_cleanup_finished_scheduled_steps';

    public const ACTION_SCHEDULER_STORED_ACTION = 'action_scheduler_stored_action';

    public const ACTION_SCHEDULER_BEGIN_EXECUTE = 'action_scheduler_begin_execute';

    public const ACTION_WORKFLOW_SAVED = 'publishpressfuture_workflow_saved';

    public const ACTION_WORKFLOW_UPDATED = 'publishpressfuture_workflow_updated';

    public const ACTION_WORKFLOW_INSERTED = 'publishpressfuture_workflow_inserted';

    public const ACTION_WORKFLOW_UNPUBLISHED = 'publishpressfuture_workflow_unpublished';

    public const ACTION_WORKFLOW_PUBLISHED = 'publishpressfuture_workflow_published';

    public const ACTION_WORKFLOW_DELETED = 'publishpressfuture_workflow_deleted';

    public const ACTION_WORKFLOW_EDITOR_SCRIPTS = 'publishpressfuture_workflow_editor_scripts';

    /** @deprecated 4.3.1 Use FILTER_WORKFLOW_TRIGGER_STEPS instead. */
    public const FILTER_WORKFLOW_TRIGGER_NODES = 'publishpressfuture_workflow_trigger_nodes';

    /** @since 4.3.1 */
    public const FILTER_WORKFLOW_TRIGGER_STEPS = 'publishpressfuture_workflow_trigger_steps';

    /** @deprecated 4.3.1 Use FILTER_WORKFLOW_ACTION_STEPS instead. */
    public const FILTER_WORKFLOW_ACTION_NODES = 'publishpressfuture_workflow_action_nodes';

    /** @since 4.3.1 */
    public const FILTER_WORKFLOW_ACTION_STEPS = 'publishpressfuture_workflow_action_steps';

    /** @deprecated 4.3.1 Use FILTER_WORKFLOW_ADVANCED_STEPS instead. */
    public const FILTER_WORKFLOW_ADVANCED_NODES = 'publishpressfuture_workflow_advanced_nodes';

    /** @since 4.3.1 */
    public const FILTER_WORKFLOW_ADVANCED_STEPS = 'publishpressfuture_workflow_advanced_steps';

    /** @deprecated 4.3.1 Use FILTER_WORKFLOW_STEP_CATEGORIES instead. */
    public const FILTER_WORKFLOW_NODE_CATEGORIES = 'publishpressfuture_workflow_node_categories';

    /** @since 4.3.1 */
    public const FILTER_WORKFLOW_STEP_CATEGORIES = 'publishpressfuture_workflow_step_categories';

    public const FILTER_WORKFLOW_ENGINE_MAP_TRIGGER = 'publishpressfuture_workflow_engine_map_trigger';

    /** @deprecated 4.3.1 Use FILTER_WORKFLOW_ENGINE_MAP_STEP_RUNNER instead. */
    public const FILTER_WORKFLOW_ENGINE_MAP_NODE_RUNNER = 'publishpressfuture_workflow_engine_map_node_runner';

    /** @since 4.3.1 */
    public const FILTER_WORKFLOW_ENGINE_MAP_STEP_RUNNER = 'publishpressfuture_workflow_engine_map_step_runner';

    public const FILTER_ACTION_SCHEDULER_LIST_COLUMN_ARGS = 'action_scheduler_list_table_column_args';

    public const FILTER_ACTION_SCHEDULER_LIST_COLUMN_RECURRENCE = 'action_scheduler_list_table_column_recurrence';

    public const FILTER_IGNORE_SAVE_POST_EVENT = 'publishpressfuture_ignore_save_post_event';

    public const FILTER_ORPHAN_WORKFLOW_ARGS_CLEANUP_INTERVAL =
        'publishpressfuture_orphan_workflow_args_cleanup_interval';

    public const FILTER_FINISHED_SCHEDULED_STEPS_CLEANUP_INTERVAL =
        'publishpressfuture_finished_scheduled_steps_cleanup_interval';

    public const FILTER_CRON_SCHEDULE_RUNNER_TRANSIENT_TIMEOUT =
        'publishpressfuture_cron_schedule_runner_transient_timeout';

    public const FILTER_CLEANUP_SCHEDULED_TRANSIENT_TIMEOUT =
        'publishpressfuture_cleanup_scheduled_transient_timeout';

    public const FILTER_INTERVAL_IN_SECONDS = 'publishpressfuture_interval_in_seconds';

    public const FILTER_THE_CONTENT = 'the_content';

    public const ACTION_CHECK_EXPIRED_ACTIONS = 'publishpressfuture_check_expired_actions';

    public const ACTION_WARN_ABOUT_PAST_DUE_ACTIONS = 'publishpressfuture_warn_about_past_due_actions';

    public const FILTER_WORKFLOW_ROUTE_VARIABLE = 'publishpressfuture_workflow_route_variable';

    public const FILTER_DUPLICATE_PREVENTION_THRESHOLD = 'publishpressfuture_duplicate_prevention_threshold';

    public const FILTER_CRON_SCHEDULE_RUNNER_ACTION_ARGS = 'publishpressfuture_cron_schedule_runner_action_args';

    public const FILTER_SHOULD_SKIP_SCHEDULING = 'publishpressfuture_should_skip_scheduling';

    public const FILTER_SHOULD_USE_TIMESTAMP_ON_ACTION_UID = 'publishpressfuture_should_use_timestamp_on_action_uid';

    public const ACTION_REGISTER_REST_ROUTES = 'publishpressfuture_register_rest_routes';

    public const FILTER_REGISTER_REST_ROUTES = 'publishpressfuture_filter_rest_routes';

    public const FILTER_THE_TITLE = 'the_title';

    public const FILTER_POST_ROW_ACTIONS = 'post_row_actions';

    public const ACTION_ADMIN_FOOTER = 'admin_footer';

    public const FILTER_POST_UPDATED_MESSAGES = 'post_updated_messages';

    public const FILTER_BULK_POST_UPDATED_MESSAGES = 'bulk_post_updated_messages';

    public const ACTION_DELETE_EXPIRED_DONE_ACTIONS = 'publishpressfuture_delete_expired_done_actions';

    /**
     * @since 4.6.0
     */
    public const ACTION_WORKFLOW_TRIGGER_EXECUTED = 'publishpressfuture_workflow_trigger_executed';

    /**
     * @since 4.7.0
     */
    public const ACTION_WORKFLOW_ENGINE_INITIALIZE_WORKFLOW = 'publishpressfuture_workflow_engine_initialize_workflow';

    /**
     * @since 4.7.0
     */
    public const ACTION_WORKFLOW_ENGINE_SETUP_TRIGGER = 'publishpressfuture_workflow_engine_setup_trigger';

    /**
     * @since 4.7.0
     */
    public const ACTION_WORKFLOW_ENGINE_SETUP_STEP = 'publishpressfuture_workflow_engine_setup_step';

    /**
     * @since 4.7.0
     */
    public const ACTION_WORKFLOW_ENGINE_EXECUTE_SCHEDULED_STEP = 'publishpressfuture_workflow_engine_execute_scheduled_step';

    /**
     * @since 4.7.0
     */
    public const ACTION_WORKFLOW_ENGINE_EXECUTE_STEP = 'publishpressfuture_workflow_engine_execute_step';

    /**
     * @since 4.7.0
     */
    public const ACTION_WORKFLOW_ENGINE_WORKFLOWS_INITIALIZED = 'publishpressfuture_workflow_engine_workflows_initialized';

    /**
     * @since 4.7.0
     */
    public const ACTION_WORKFLOW_ENGINE_START_ENGINE = 'publishpressfuture_workflow_engine_start_engine';

    /**
     * @since 4.7.0
     */
    public const ACTION_WORKFLOW_ENGINE_RUN_WORKFLOWS = 'publishpressfuture_workflow_engine_run_workflows';
}
