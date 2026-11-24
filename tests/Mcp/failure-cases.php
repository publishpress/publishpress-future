<?php

/**
 * Validate failure between native writes and fail-closed permissions.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

$admin = $call('get-plugin-settings', ['group' => 'admin']);
$blockColumn = static function ($new, $old) {
    return $old;
};
add_filter('pre_update_option_expirationdateColumnStyle', $blockColumn, 100, 2);
try {
    $failed = $call('update-plugin-settings', ['group' => 'admin', 'expected_hash' => $admin['hash'],
        'settings' => ['columnStyle' => $admin['settings']->columnStyle === 'simple' ? 'verbose' : 'simple']]);
    $check(is_wp_error($failed), 'Native settings persistence failure is reported');
    $check($call('get-plugin-settings', ['group' => 'admin'])['hash'] === $admin['hash'], 'Failed settings write preserves original state');
} finally {
    remove_filter('pre_update_option_expirationdateColumnStyle', $blockColumn, 100);
}
$general = $call('get-plugin-settings', ['group' => 'general']);
$blockRoles = static function ($new, $old) {
    return $old;
};
add_filter('pre_update_option_' . wp_roles()->role_key, $blockRoles, 100, 2);
try {
    $failed = $call('update-plugin-settings', ['group' => 'general', 'expected_hash' => $general['hash'],
        'settings' => ['allowUserRoles' => ['administrator', 'subscriber']]]);
    $check(is_wp_error($failed), 'Failed role-capability option write is not hidden by an in-memory role cache');
    $check($call('get-plugin-settings', ['group' => 'general'])['hash'] === $general['hash'], 'Role-write recovery retains original grant settings');
} finally {
    remove_filter('pre_update_option_' . wp_roles()->role_key, $blockRoles, 100);
}
$beforeWorkflow = $call('get-workflow', ['id' => $workflowId]);
$blockTitle = static function ($data, $postarr) use ($workflowId, $beforeWorkflow) {

    if (($postarr['ID'] ?? 0) === $workflowId) {
        $data['post_title'] = $beforeWorkflow['title'];
    }
    return $data;
};
add_filter('wp_insert_post_data', $blockTitle, 100, 2);
try {
    $failed = $call('save-workflow', ['id' => $workflowId, 'expected_hash' => $beforeWorkflow['hash'], 'title' => 'Blocked fixture title']);
    $check(is_wp_error($failed), 'Native workflow persistence failure is reported');
    $check($call('get-workflow', ['id' => $workflowId])['hash'] === $beforeWorkflow['hash'], 'Workflow recovery restores graph, debug options and revision state');
} finally {
    remove_filter('wp_insert_post_data', $blockTitle, 100);
}
$workflowMetadataKeys = [
    \PublishPress\Future\Modules\Workflows\Models\WorkflowModel::META_KEY_HAS_LEGACY_TRIGGER,
    \PublishPress\Future\Modules\Workflows\Models\WorkflowModel::META_KEY_HAS_MANUAL_TRIGGER,
    \PublishPress\Future\Modules\Workflows\Models\WorkflowModel::META_KEY_DEBUG_RAY_SHOW_QUERIES,
    \PublishPress\Future\Modules\Workflows\Models\WorkflowModel::META_KEY_DEBUG_RAY_SHOW_EMAILS,
    \PublishPress\Future\Modules\Workflows\Models\WorkflowModel::META_KEY_DEBUG_RAY_SHOW_WORDPRESS_ERRORS,
    \PublishPress\Future\Modules\Workflows\Models\WorkflowModel::META_KEY_DEBUG_RAY_SHOW_CURRENT_RUNNING_STEP,
];
$workflowMetadataSnapshot = get_post_meta($workflowId);
foreach ($workflowMetadataKeys as $key) {
    delete_post_meta($workflowId, $key);
}
add_filter('wp_insert_post_data', $blockTitle, 100, 2);
try {
    $missingMetadata = $call('get-workflow', ['id' => $workflowId]);
    $failed = $call('save-workflow', ['id' => $workflowId, 'expected_hash' => $missingMetadata['hash'], 'title' => 'Blocked missing-metadata title']);
    $check(is_wp_error($failed), 'Native workflow failure with initially absent metadata is reported');
    foreach ($workflowMetadataKeys as $key) {
        $check(!metadata_exists('post', $workflowId, $key), 'Recovery removes a newly created native workflow key: ' . $key);
    }
    $check($call('get-workflow', ['id' => $workflowId])['hash'] === $missingMetadata['hash'], 'Recovery restores the exact revision when native metadata was initially absent');
} finally {
    remove_filter('wp_insert_post_data', $blockTitle, 100);
    foreach ($workflowMetadataKeys as $key) {
        delete_post_meta($workflowId, $key);
        foreach ($workflowMetadataSnapshot[$key] ?? [] as $value) {
            add_post_meta($workflowId, $key, wp_slash(maybe_unserialize($value)));
        }
    }
}
$workflowService = $container->get($ids::MCP_WORKFLOWS);
$facadeProperty = new ReflectionProperty($workflowService, 'wordpress');
$facadeProperty->setAccessible(true);
$disappearing = new class ($workflowId) extends \PublishPress\Future\Framework\WordPress\Facade\McpFacade {
    private int $target;
    private int $reads = 0;

    public function __construct(int $target)
    {
        $this->target = $target;
    }

    public function post(int $id): ?\WP_Post
    {
        if ($id === $this->target && ++$this->reads > 1) {
            return null;
        }
        return parent::post($id);
    }
};
$facadeProperty->setValue($workflowService, $disappearing);
try {
    $failed = $call('save-workflow', ['id' => $workflowId, 'expected_hash' => $beforeWorkflow['hash'], 'title' => 'Disappearing target']);
    $check(is_wp_error($failed) && ($failed->get_error_data()['status'] ?? 0) === 409, 'A disappearing workflow returns a conflict before snapshot cloning or writes');
} finally {
    $facadeProperty->setValue($workflowService, $wordpress);
}
$check($call('get-workflow', ['id' => $workflowId])['hash'] === $beforeWorkflow['hash'], 'Disappearing-target handling changes no workflow data');
$posts[] = $secondPost = wp_insert_post(['post_title' => 'MCP second batch target', 'post_status' => 'draft', 'post_author' => $users['administrator']]);
$firstState = $call('get-post-action', ['post_id' => $postId]);
$secondState = $call('get-post-action', ['post_id' => $secondPost]);
$changes = [];
foreach ([$firstState, $secondState] as $state) {
    $changes[] = ['post_id' => $state['post_id'], 'expected_hash' => $state['hash'], 'enabled' => true,
        'date' => gmdate('c', time() + 86400 * 5), 'action' => 'change-status', 'new_status' => 'draft'];
}
$blockSecond = static function ($pre, $timestamp, $hook, $args) use ($secondPost) {

    return ($args['postId'] ?? 0) === $secondPost ? 0 : $pre;
};
add_filter('pre_as_schedule_single_action', $blockSecond, 100, 4);
try {
    $failed = $call('bulk-update-post-actions', ['changes' => $changes]);
    $check(is_wp_error($failed), 'Failed final batch schedule is reported');
    $check($call('get-post-action', ['post_id' => $postId])['hash'] === $firstState['hash'], 'Failed batch compensates the earlier schedule');
    $check(!$call('get-post-action', ['post_id' => $secondPost])['settings']->enabled, 'Failed batch leaves no enabled partial target');
} finally {
    remove_filter('pre_as_schedule_single_action', $blockSecond, 100);
}
$invalidBatch = $changes;
$invalidBatch[1]['post_id'] = 99999999;
$check(is_wp_error($call('bulk-update-post-actions', ['changes' => $invalidBatch])), 'Bulk rejects an inaccessible final object before writing');
$check($call('get-post-action', ['post_id' => $postId])['hash'] === $firstState['hash'], 'Preflight failure preserves earlier objects');
$check(is_wp_error($call('get-post-action', ['post_id' => (string) $postId])), 'Numeric string IDs return a validation error before permission callbacks');
$check(is_wp_error($call('bulk-update-post-actions', ['changes' => 'bad'])), 'Malformed bulk values cannot crash permission callbacks');
$check(is_wp_error($call('save-workflow', ['title' => 'Malformed graph', 'flow' => ['nodes' => [['id' => 'bad', 'data' => ['name' => ['bad']]]], 'edges' => []]])), 'Malformed graph node names return a bounded error');
$denyPublish = static function ($caps) {
    $caps['publish_publishpress_workflows'] = false;
    return $caps;
};
add_filter('user_has_cap', $denyPublish, 100);
try {
    $state = $call('get-workflow', ['id' => $workflowId]);
    $check(!is_wp_error($state), 'A publisher capability denial still permits authorized workflow reads');
    $check(is_wp_error($call('save-workflow', ['id' => $workflowId, 'expected_hash' => $state['hash'], 'title' => 'Forbidden active edit'])), 'Editing an active workflow requires publish capability even for administrators');
} finally {
    remove_filter('user_has_cap', $denyPublish, 100);
}
$foreignAction = as_schedule_single_action(time() + 86400, 'fixture_foreign_hook', [], 'fixture-foreign');
try {
    $check(is_wp_error($call('get-scheduled-action', ['id' => $foreignAction])), 'Foreign Action Scheduler groups are never exposed');
} finally {
    \ActionScheduler::store()->delete_action($foreignAction);
}

$runtimeService = $container->get($ids::MCP_RUNTIME);
$storeProperty = new ReflectionProperty($runtimeService, 'store');
$storeProperty->setAccessible(true);
$originalStore = $storeProperty->getValue($runtimeService);
$failedStore = new class extends \ActionScheduler_DBStore {
    // phpcs:ignore PSR1.Methods.CamelCapsMethodName.NotCamelCaps -- Override the native Action Scheduler method.
    public function query_actions($query = [], $query_type = 'select')
    {
        return null;
    }
};
$storeProperty->setValue($runtimeService, $failedStore);
try {
    $check(is_wp_error($call('list-scheduled-actions')), 'A failed native scheduler query is reported rather than returned as an empty successful page');
} finally {
    $storeProperty->setValue($runtimeService, $originalStore);
}
