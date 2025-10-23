<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Modules\Expirator\Tables;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Core\Plugin;
use PublishPress\Future\Modules\Expirator\Adapters\CronToWooActionSchedulerAdapter;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\HooksAbstract;
use PublishPress\Future\Modules\Expirator\Models\PostTypeModel;

defined('ABSPATH') or die('Direct access not allowed.');

// phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
class ScheduledActionsTable extends \ActionScheduler_ListTable
{
    /**
     * @var \PublishPress\Future\Core\HookableInterface
     */
    private $hooks;

    /**
     * Array of seconds for common time periods, like week or month,
     * alongside an internationalised string representation, i.e.
     * "Day" or "Days".
     *
     * @var array
     */
    private static $time_periods;

    public function __construct(
        \ActionScheduler_Store $store,
        \ActionScheduler_Logger $logger,
        \ActionScheduler_QueueRunner $runner,
        HookableInterface $hooks
    ) {
        parent::__construct($store, $logger, $runner);

        $this->hooks = $hooks;

        $this->table_header = __('Scheduled Actions', 'post-expirator');

        unset($this->columns['group']);
        $this->columns['hook'] = __('Action', 'post-expirator');

        // Force the title of columns so they are translatable on our text domain.
        $this->columns['status'] = __('Status', 'post-expirator');
        $this->columns['args'] = __('Arguments', 'post-expirator');
        $this->columns['log_entries'] = __('Logs', 'post-expirator');
        $this->columns['schedule'] = __('Scheduled Date', 'post-expirator');
        $this->columns['recurrence'] = __('Recurrence', 'post-expirator');


        $this->hooks->addAction('admin_enqueue_scripts', [$this, 'enqueueScripts']);

        $this->row_actions = array(
            'hook' => array(
                'run' => array(
                    'name' => __('Run', 'post-expirator'),
                    'desc' => __('Process the action now', 'post-expirator'),
                ),
                'cancel' => array(
                    'name' => __('Cancel', 'post-expirator'),
                    'desc' => __(
                        'Cancel the action. This will prevent the action from running in the future',
                        'post-expirator'
                    ),
                    'class' => 'cancel trash',
                ),
            ),
        );

        $this->bulk_actions = array(
            'delete' => __('Delete', 'post-expirator'),
            'run' => __('Run', 'post-expirator'),
            'cancel' => __('Cancel', 'post-expirator'),
        );

        self::$time_periods = array(
            array(
                'seconds' => YEAR_IN_SECONDS,
                /* translators: %s: amount of time */
                'names'   => _n_noop('%s year', '%s years', 'post-expirator'),
            ),
            array(
                'seconds' => MONTH_IN_SECONDS,
                /* translators: %s: amount of time */
                'names'   => _n_noop('%s month', '%s months', 'post-expirator'),
            ),
            array(
                'seconds' => WEEK_IN_SECONDS,
                /* translators: %s: amount of time */
                'names'   => _n_noop('%s week', '%s weeks', 'post-expirator'),
            ),
            array(
                'seconds' => DAY_IN_SECONDS,
                /* translators: %s: amount of time */
                'names'   => _n_noop('%s day', '%s days', 'post-expirator'),
            ),
            array(
                'seconds' => HOUR_IN_SECONDS,
                /* translators: %s: amount of time */
                'names'   => _n_noop('%s hour', '%s hours', 'post-expirator'),
            ),
            array(
                'seconds' => MINUTE_IN_SECONDS,
                /* translators: %s: amount of time */
                'names'   => _n_noop('%s minute', '%s minutes', 'post-expirator'),
            ),
            array(
                'seconds' => 1,
                /* translators: %s: amount of time */
                'names'   => _n_noop('%s second', '%s seconds', 'post-expirator'),
            ),
        );

        $this->sort_by = [
            'action_id',
            'schedule',
            'hook',
            'group',
            'status',
        ];
    }

    public function enqueueScripts()
    {
        wp_enqueue_script('jquery-ui-dialog');
        wp_enqueue_script(
            'publishpress-future-future-actions',
            Plugin::getScriptUrl('futureActions'),
            [
                'jquery',
                'jquery-ui-dialog',
            ],
            PUBLISHPRESS_FUTURE_VERSION,
            true
        );
        wp_localize_script(
            'publishpress-future-future-actions',
            'publishpressFutureActionsConfig',
            [
                'dialogTitle' => esc_js(__('Action Logs', 'post-expirator')),
            ]
        );

        wp_enqueue_style('wp-jquery-ui-dialog');
    }

    protected function get_request_orderby()
    {

        $valid_sortable_columns = array_values($this->sort_by);

        if (! empty($_GET['orderby']) && in_array($_GET['orderby'], $valid_sortable_columns, true)) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $orderby = sanitize_text_field(wp_unslash($_GET['orderby'])); //phpcs:ignore WordPress.Security.NonceVerification.Recommended
        } else {
            $orderby = $valid_sortable_columns[0];
        }

        return $orderby;
    }

    protected function get_request_order()
    {
        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        $order = isset($_GET['order']) ? strtolower(
            sanitize_text_field(wp_unslash($_GET['order']))
        ) : '';

        if (empty($order)) {
            $order = 'desc';
        }

        return 'desc' === $order ? 'DESC' : 'ASC';
        // phpcs:enable WordPress.Security.NonceVerification.Recommended
    }

    /**
     * {@inheritDoc}
     */
    public function prepare_items()
    {
        $this->process_bulk_action();
        $this->process_row_actions();

        $this->prepare_column_headers();

        $per_page = $this->get_items_per_page($this->get_per_page_option_name(), $this->items_per_page);

        $query = array(
            'per_page' => $per_page,
            'offset' => $this->get_items_offset(),
            'status' => $this->get_request_status(),
            'orderby' => $this->get_request_orderby(),
            'order' => $this->get_request_order(),
            'search' => $this->get_request_search_query(),
            'group' => CronToWooActionSchedulerAdapter::SCHEDULED_ACTION_GROUP,
        );

        /**
         * Change query arguments to query for past-due actions.
         * Past-due actions have the 'pending' status and are in the past.
         * This is needed because registering 'past-due' as a status is overkill.
         */
        if ('past-due' === $this->get_request_status()) {
            $query['status'] = \ActionScheduler_Store::STATUS_PENDING;
            $query['date'] = as_get_datetime_object();
        }

        $this->items = array();

        $total_items = $this->store->query_actions($query, 'count');

        $status_labels = $this->store->get_status_labels();

        foreach ($this->store->query_actions($query) as $action_id) {
            try {
                $action = $this->store->fetch_action($action_id);
            } catch (\Exception $e) {
                continue;
            }

            if (is_a($action, 'ActionScheduler_NullAction')) {
                continue;
            }

            $this->items[$action_id] = array(
                'ID' => $action_id,
                'hook' => $action->get_hook(),
                'status_name' => $this->store->get_status($action_id),
                'status' => $status_labels[$this->store->get_status($action_id)],
                'args' => $action->get_args(),
                'group' => $action->get_group(),
                'log_entries' => $this->logger->get_logs($action_id),
                'claim_id' => $this->store->get_claim_id($action_id),
                'recurrence' => $this->get_recurrence($action),
                'schedule' => $action->get_schedule(),
            );
        }

        $this->set_pagination_args(array(
            'total_items' => $total_items,
            'per_page' => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ));
    }

    protected function extra_action_counts()
    {
        $extra_actions = array();

        $pastdue_action_counts = (int)$this->store->query_actions([
            'status' => \ActionScheduler_Store::STATUS_PENDING,
            'date' => as_get_datetime_object(),
            'group' => CronToWooActionSchedulerAdapter::SCHEDULED_ACTION_GROUP,
        ], 'count');

        if ($pastdue_action_counts) {
            $extra_actions['past-due'] = $pastdue_action_counts;
        }

        /**
         * Allows 3rd party code to add extra action counts (used in filters in the list table).
         *
         * @param $extra_actions array Array with format action_count_identifier => action count.
         * @since 3.5.0
         */
        return $this->hooks->applyFilters('action_scheduler_extra_action_counts', $extra_actions);
    }

    protected function update_status_counts()
    {
        $this->status_counts = $this->store->action_counts();

        // Update the status count
        foreach ($this->status_counts as $status => $count) {
            $query = array(
                'status' => $status,
                'group' => CronToWooActionSchedulerAdapter::SCHEDULED_ACTION_GROUP,
            );
            $this->status_counts[$status] = $this->store->query_actions($query, 'count');
        }

        $this->status_counts = array_merge($this->status_counts, $this->extra_action_counts());
    }

    protected function display_filter_by_status()
    {
        $this->update_status_counts();

        $status_list_items = array();
        $request_status = $this->get_request_status();

        // Helper to set 'all' filter when not set on status counts passed in.
        if (! isset($this->status_counts['all'])) {
            $this->status_counts = array('all' => array_sum($this->status_counts)) + $this->status_counts;
        }

        $status_labels = [
            'uninitialized' => __('Uninitialized', 'post-expirator'),
            'pending' => __('Scheduled', 'post-expirator'),
            'complete' => __('Complete', 'post-expirator'),
            'failed' => __('Failed', 'post-expirator'),
            'canceled' => __('Canceled', 'post-expirator'),
            'running' => __('Running', 'post-expirator'),
            'all' => __('All', 'post-expirator'),
        ];

        foreach ($this->status_counts as $status_name => $count) {
            if (0 === $count) {
                continue;
            }

            if ($status_name === $request_status || (empty($request_status) && 'all' === $status_name)) {
                $status_list_item = '<li class="%1$s"><a href="%2$s" class="current">%3$s</a> (%4$d)</li>';
            } else {
                $status_list_item = '<li class="%1$s"><a href="%2$s">%3$s</a> (%4$d)</li>';
            }

            $status_filter_url = ('all' === $status_name) ? remove_query_arg('status') : add_query_arg(
                'status',
                $status_name
            );
            $status_filter_url = remove_query_arg(array('paged', 's'), $status_filter_url);
            $status_list_items[] = sprintf(
                $status_list_item,
                esc_attr($status_name),
                esc_url($status_filter_url),
                esc_html(isset($status_labels[$status_name]) ? $status_labels[$status_name] : ucfirst($status_name)),
                absint($count)
            );
        }

        if ($status_list_items) {
            echo '<ul class="subsubsub">';
            echo implode(" | \n", $status_list_items); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</ul>';
        }
    }

    protected function get_search_box_button_text()
    {
        return __('Search hook, args and claim ID', 'post-expirator');
    }

    public function column_action(array $row)
    {
        return 't';
    }

    public function column_status(array $row)
    {
        $icons = [
            \ActionScheduler_Store::STATUS_COMPLETE =>
                'dashicons dashicons-yes-alt action-scheduler-status-icon-complete',
            \ActionScheduler_Store::STATUS_PENDING => 'dashicons dashicons-clock action-scheduler-status-icon-pending',
            \ActionScheduler_Store::STATUS_RUNNING => 'dashicons dashicons-update action-scheduler-status-icon-running',
            \ActionScheduler_Store::STATUS_FAILED => 'dashicons dashicons-warning action-scheduler-status-icon-failed',
            \ActionScheduler_Store::STATUS_CANCELED =>
                'dashicons dashicons-marker action-scheduler-status-icon-canceled',
        ];

        $iconClass = 'dashicons dashicons-editor-help';
        if (isset($icons[$row['status_name']])) {
            $iconClass = $icons[$row['status_name']];
        }

        $status = $row['status'];
        if ($row['status_name'] === \ActionScheduler_Store::STATUS_PENDING) {
            $status = __('Scheduled', 'post-expirator');
        }

        if ($row['status_name'] === \ActionScheduler_Store::STATUS_COMPLETE) {
            $status = __('Completed', 'post-expirator');
        }

        return '<span class="' . esc_attr($iconClass) . '"></span> ' . esc_html($status);
    }

    private function rowIsAWorkflow(array $row)
    {
        return in_array($row['hook'], [HooksAbstract::ACTION_RUN_WORKFLOW, HooksAbstract::ACTION_LEGACY_RUN_WORKFLOW])
        && isset($row['args']['workflow'])
        && $row['args']['workflow'] === 'expire';
    }

    public function column_hook(array $row)
    {
        $columnHtml = '<span title="' . esc_attr($row['hook']) . '">';

        if ($this->rowIsAWorkflow($row)) {
            $columnHtml .= $this->render_expiration_hook_action($row);
        } else {
            $columnHtml .= esc_html(
                $this->hooks->applyFilters(
                    HooksAbstract::FILTER_ACTION_SCHEDULER_LIST_COLUMN_HOOK,
                    $row['hook'] . " [{$row['ID']}]",
                    $row
                )
            );
        }
        $columnHtml .= '</span>';

        $columnHtml .= $this->maybe_render_actions($row, 'hook');

        return $columnHtml;
    }

    private function render_expiration_hook_action(array $row)
    {
        $actionData = $this->getActionData($row);
        $actionLabel = $actionData['actionLabel'];

        if (empty($actionLabel)) {
            $container = Container::getInstance();
            $argsModelFactory = $container->get(ServicesAbstract::ACTION_ARGS_MODEL_FACTORY);

            /**
             * @var \PublishPress\Future\Modules\Expirator\Interfaces\ActionArgsModelInterface
             */
            $argsModel = $argsModelFactory();
            $argsModel->loadByActionId($row['ID']);

            // Post type
            $postType = $argsModel->getArg('postType');
            if (empty($postType)) {
                $postType = $argsModel->getArg('post_type');
            }
            if (empty($postType)) {
                $container = Container::getInstance();
                $factory = $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
                $postModel = $factory($row['args']['postId']);

                $postType = $postModel->getPostType();
            }
            $postTypeModel = new PostTypeModel();
            $postTypeModel->load($postType);

            $actionLabel = $argsModel->getActionLabel($postModel->getPostType());
        }

        return esc_html($actionLabel);
    }

    public function column_args(array $row)
    {
        if (empty($row['args'])) {
            return $this->hooks->applyFilters('action_scheduler_list_table_column_args', '', $row);
        }

        $columnHtml = '';
        if ($this->rowIsAWorkflow($row)) {
            $columnHtml = $this->render_expiration_hook_args($row);
        } else {
            $columnHtml = '<ul>';
            foreach ($row['args'] as $key => $value) {
                $columnHtml .= sprintf(
                    '<li><code>%s => %s</code></li>',
                    esc_html(var_export($key, true)), // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
                    // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
                    esc_html(
                        var_export($value, true) // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
                    )
                );
            }

            $columnHtml .= '</ul>';
        }

        return $this->hooks->applyFilters('action_scheduler_list_table_column_args', $columnHtml, $row);
    }

    public function column_recurrence($row)
    {
        $action = $this->store->fetch_action($row['ID']);
        $html = $this->get_recurrence($action);

        return $this->hooks->applyFilters('action_scheduler_list_table_column_recurrence', $html, $row);
    }

    private function render_expiration_hook_args(array $row)
    {
        $actionData = $this->getActionData($row);

        $columnHtml = sprintf(
            // translators: %1$s: post type label, %2$d: post ID, %3$s: post link tag start, %4$s: post title, %5$s: post link tag end
            esc_html__('%1$s: [%2$d] %3$s%4$s%5$s', 'post-expirator'),
            esc_html($actionData['postTypeLabel']),
            $actionData['postId'],
            '<a href="' . esc_url($actionData['postLink'] ?? '') . '">',
            esc_html($actionData['postTitle'] ?? ''),
            '</a>'
        );

        $taxonomyActions = [
            ExpirationActionsAbstract::POST_CATEGORY_SET,
            ExpirationActionsAbstract::POST_CATEGORY_REMOVE,
            ExpirationActionsAbstract::POST_CATEGORY_ADD
        ];

        $container = Container::getInstance();
        $argsModelFactory = $container->get(ServicesAbstract::ACTION_ARGS_MODEL_FACTORY);

        /**
         * @var \PublishPress\Future\Modules\Expirator\Interfaces\ActionArgsModelInterface
         */
        $argsModel = $argsModelFactory();
        $argsModel->loadByActionId($row['ID']);

        $action = $argsModel->getAction();

        if ($action === ExpirationActionsAbstract::CHANGE_POST_STATUS) {
            $newStatus = $argsModel->getArg('newStatus');
            $status = get_post_status_object($newStatus);
            $statusName = $newStatus;
            if (is_object($status)) {
                $statusName = $status->label;
            }

            $columnHtml .= sprintf(
                // translators: %s is the new status
                '<br />' . esc_html__('New Status: %s', 'post-expirator'),
                esc_html($statusName)
            );
        }

        if (in_array($action, $taxonomyActions)) {
            $columnHtml .= sprintf(
                // translators: %s is the list of terms
                '<br />' . esc_html__('Terms: %s', 'post-expirator'),
                implode(', ', $argsModel->getTaxonomyTermsNames())
            );
        }

        return $columnHtml;
    }

    private function getActionData(array $row): array
    {
        $container = Container::getInstance();
        $factory = $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
        $postModel = $factory($row['args']['postId']);

        $argsModelFactory = $container->get(ServicesAbstract::ACTION_ARGS_MODEL_FACTORY);
        /**
         * @var \PublishPress\Future\Modules\Expirator\Interfaces\ActionArgsModelInterface
         */
        $argsModel = $argsModelFactory();
        $argsModel->loadByActionId($row['ID']);

        // Post type
        $postType = $argsModel->getArg('postType');
        if (empty($postType)) {
            $postType = $argsModel->getArg('post_type');
        }
        if (empty($postType)) {
            $postType = $postModel->getPostType();
        }
        $postTypeModel = new PostTypeModel();
        $postTypeModel->load($postType);

        $postTypeLabel = $postType;
        if (! empty($postTypeModel)) {
            $postTypeLabel = $postTypeModel->getLabel();
        }

        // Title
        $postTitle = $postModel->getTitle();
        if (empty($postTitle)) {
            $postTitle = $argsModel->getArg('postTitle');
        }
        if (empty($postTitle)) {
            $postTitle = $argsModel->getArg('post_title');
        }

        // Post link
        $postLink = $argsModel->getArg('postLink');
        if (empty($postLink)) {
            $postLink = $argsModel->getArg('post_link');
        }
        if (empty($postLink)) {
            $postLink = $postModel->getPostEditLink();
        }

        // Action label
        $actionLabel = $argsModel->getActionLabel($postType);
        if (empty($actionLabel)) {
            $actionLabel = $argsModel->getArg('actionLabel');
        }
        if (empty($actionLabel)) {
            $actionLabel = $postModel->getExpirationType();
        }

        return [
            'postId' => $postModel->getPostId(),
            'postType' => $postType,
            'postTypeLabel' => $postTypeLabel,
            'postTitle' => $postTitle,
            'postLink' => $postLink,
            'actionLabel' => $actionLabel,
        ];
    }

    /**
     * Prints the logs entries inline. We do so to avoid loading Javascript and other hacks to show it in a modal.
     *
     * @param array $row Action array.
     * @return string
     */
    public function column_log_entries(array $row)
    {
        $timezone = new \DateTimeZone('UTC');

        $userLogFormat = get_user_meta(get_current_user_id(), 'publishpressfuture_actions_log_format', true);

        $html = '';

        if (! in_array((string)$userLogFormat, ['list', 'popup'])) {
            $userLogFormat = 'popup';
        }

        if ($userLogFormat === 'list') {
            $html = '<ol>';

            foreach ($row['log_entries'] as $logEntry) {
                $html .= $this->get_log_entry_html($logEntry, $timezone);
            }

            $html .= '</ol>';
        }

        if ($userLogFormat === 'popup') {
            $html = '<a href="javascript:void(0);" class="publishpres-future-view-log" data-id="' . $row['ID'] . '">' . esc_html__(
                'View log',
                'post-expirator'
            ) . '</a>';
            $html .= '<div class="publishpress-future-log-entries-popup publishpress-future-log-' . $row['ID'] . '" style="display: none;">';
            $html .= '<div>';

            $html .= '<table>';
            $html .= '<tbody>';

            $html .= '<tr>';
            $html .= '<td>' . esc_html__('Action: ', 'post-expirator') . '</td><td>' . $this->column_hook(
                $row
            ) . '</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td>' . esc_html__('Status: ', 'post-expirator') . '</td><td>' . $this->column_status(
                $row
            ) . '</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td>' . esc_html__('Arguments: ', 'post-expirator') . '</td><td>' . $this->column_args(
                $row
            ) . '</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td>' . esc_html__('Scheduled date: ', 'post-expirator') . '</td><td>' . $this->column_schedule(
                $row
            ) . '</td>';
            $html .= '</tr>';

            $html .= '</tbody>';
            $html .= '</table>';


            $html .= '<br />';

            $html .= '<table class="wp-list-table widefat striped">';
            $html .= '<thead>';
            $html .= '<tr>';
            $html .= '<th>' . esc_html__('Date', 'post-expirator') . '</th>';
            $html .= '<th>' . esc_html__('Message', 'post-expirator') . '</th>';
            $html .= '</tr>';
            $html .= '</thead>';
            $html .= '<tbody>';

            foreach ($row['log_entries'] as $logEntry) {
                $date = $logEntry->get_date();
                $date->setTimezone($timezone);

                $html .= '<tr>';
                $html .= '<td>' . esc_html($date->format('Y-m-d H:i:s O')) . '</td>';
                $html .= '<td>' . esc_html(ucfirst($logEntry->get_message())) . '</td>';
                $html .= '</tr>';
            }

            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Get the scheduled date in a human friendly format.
     *
     * @param \ActionScheduler_Schedule $schedule
     * @return string
     */
    protected function get_schedule_display_string(\ActionScheduler_Schedule $schedule)
    {
        $schedule_display_string = '';

        if (is_a($schedule, 'ActionScheduler_NullSchedule')) {
            return __('Async', 'post-expirator');
        }

        if (! method_exists($schedule, 'next') || ! $schedule->get_date()) {
            return '0000-00-00 00:00:00';
        }

        $next_timestamp = $schedule->get_date()->getTimestamp();

        $gmt_schedule_display_string = $schedule->get_date()->format('Y-m-d H:i:s O');
        $schedule_display_string .= wp_date('Y-m-d H:i:s O', $next_timestamp);
        $schedule_display_string .= '<br/>';

        if (gmdate('U') > $next_timestamp) {
            /* translators: %s: date interval */
            $schedule_display_string .= sprintf(
                // translators: %s is the date interval in human readable format in the past
                __(' (%s ago)', 'post-expirator'),
                self::human_interval(gmdate('U') - $next_timestamp)
            );
        } else {
            $schedule_display_string .= sprintf(
                // translators: %s is the date interval in human readable format in the present or future
                __(' (%s)', 'post-expirator'),
                self::human_interval($next_timestamp - gmdate('U'))
            );
        }

        return '<span title="' . esc_attr($gmt_schedule_display_string) . '">' . $schedule_display_string . '</span>';
    }

    /**
     * Convert an interval of seconds into a two part human friendly string.
     *
     * The WordPress human_time_diff() function only calculates the time difference to one degree, meaning
     * even if an action is 1 day and 11 hours away, it will display "1 day". This function goes one step
     * further to display two degrees of accuracy.
     *
     * Inspired by the Crontrol::interval() function by Edward Dale: https://wordpress.org/plugins/wp-crontrol/
     *
     * @param int $interval A interval in seconds.
     * @param int $periods_to_include Depth of time periods to include, e.g. for an interval of 70, and $periods_to_include of 2, both minutes and seconds would be included. With a value of 1, only minutes would be included.
     * @return string A human friendly string representation of the interval.
     */
    private static function human_interval($interval, $periods_to_include = 2)
    {
        if ($interval <= 0) {
            return __('Now!', 'post-expirator');
        }

        $output = '';

        for (
            $time_period_index = 0, $periods_included = 0, $seconds_remaining = $interval; $time_period_index < count(
                self::$time_periods
            ) && $seconds_remaining > 0 && $periods_included < $periods_to_include; $time_period_index++
        ) {
            $periods_in_interval = floor($seconds_remaining / self::$time_periods[$time_period_index]['seconds']);

            if ($periods_in_interval > 0) {
                if (! empty($output)) {
                    $output .= ' ';
                }
                $output .= sprintf(
                    _n(
                        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralSingle
                        self::$time_periods[$time_period_index]['names'][0],
                        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralPlural
                        self::$time_periods[$time_period_index]['names'][1],
                        $periods_in_interval,
                        'post-expirator'
                    ),
                    $periods_in_interval
                );
                $seconds_remaining -= $periods_in_interval * self::$time_periods[$time_period_index]['seconds'];
                $periods_included++;
            }
        }

        return $output;
    }

    /**
     * Returns the recurrence of an action or 'Non-repeating'. The output is human readable.
     *
     * @param ActionScheduler_Action $action
     *
     * @return string
     */
    protected function get_recurrence($action)
    {
        $schedule = $action->get_schedule();
        if ($schedule->is_recurring() && method_exists($schedule, 'get_recurrence')) {
            $recurrence = $schedule->get_recurrence();

            if (is_numeric($recurrence)) {
                /* translators: %s: time interval */
                return sprintf(__('Every %s', 'post-expirator'), self::human_interval($recurrence));
            } else {
                return $recurrence;
            }
        }

        return __('Non-repeating', 'post-expirator');
    }

    /**
     * Message to be displayed when there are no items
     *
     * @since 3.1.0
     */
    public function no_items()
    {
        echo esc_html__('No Scheduled Actions.', 'post-expirator');
    }

    /**
     * Bulk action handler for running actions immediately.
     *
     * @param array $ids Array of action IDs to run
     * @param string $ids_sql
     */
    protected function bulk_run(array $ids, $ids_sql)
    {
        foreach ($ids as $action_id) {
            try {
                $this->runner->process_action($action_id);
            } catch (\Exception $e) {
                continue;
            }
        }
    }

    /**
     * Bulk action handler for canceling actions.
     *
     * @param array $ids Array of action IDs to cancel
     * @param string $ids_sq
     */
    protected function bulk_cancel(array $ids, $ids_sql)
    {
        foreach ($ids as $action_id) {
            try {
                $this->store->cancel_action($action_id);
            } catch (\Exception $e) {
                continue;
            }
        }
    }
}

// phpcs:enable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
