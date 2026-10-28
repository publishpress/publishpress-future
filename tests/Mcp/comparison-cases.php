<?php

/**
 * Cross-plugin contract checks against native Future formats and individual permissions.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

$nativeSettings = $container->get($ids::SETTINGS);
foreach (['post', 'page'] as $type) {
    $state = $success($call('get-post-type-settings', ['post_type' => $type]), 'Read native defaults for ' . $type);
    $nativeBefore = $nativeSettings->getPostTypeDefaults($type);
    foreach ($state['schema']['properties'] as $field => $schema) {
        $value = $state['settings']->$field;
        $check(!is_wp_error(rest_validate_value_from_schema($value, $schema)), $type . '/' . $field . ' exposes its exact native field format');
        $input = ['post_type' => $type, 'expected_hash' => $state['hash'], 'settings' => [$field => $value]];
        $preview = $success($call('update-post-type-settings', $input + ['dry_run' => true]), $type . '/' . $field . ' native preview');
        $saved = $success($call('update-post-type-settings', $input), $type . '/' . $field . ' native save');
        $check($saved['settings']->$field === $preview['settings']->$field, $type . '/' . $field . ' native read-back matches preview');
        $state = $saved;
        $badValues = $schema['type'] === 'boolean' ? ['false', 'true', 0, 1, null]
            : ($schema['type'] === 'array' ? ['1', [true], ['1'], ['term' => 1], null] : [false, 123, [], null]);
        foreach ($badValues as $bad) {
            $check(is_wp_error($call('update-post-type-settings', ['post_type' => $type, 'expected_hash' => $state['hash'],
                'settings' => [$field => $bad]])), $type . '/' . $field . ' rejects an incompatible format');
            $check($call('get-post-type-settings', ['post_type' => $type])['hash'] === $state['hash'], 'Malformed post-type patch saves nothing');
        }
    }
    $nativeAfter = $nativeSettings->getPostTypeDefaults($type);
    $check(array_diff_key($nativeAfter, $state['schema']['properties']) === array_diff_key($nativeBefore, $state['schema']['properties']), 'Native extension-owned defaults are preserved');
}

if ($wordpress->pro()) {
    $proState = $call('get-pro-settings', ['post_type' => 'post']);
    foreach ($proState['schema']['properties'] as $field => $schema) {
        $badValues = $schema['type'] === 'boolean' ? ['false', 0, null]
            : ($schema['type'] === 'array' ? ['draft', [true], ['named' => 'draft'], null]
            : ($schema['type'] === 'object' ? ['mapping', ['unknown' => true], null] : [false, [], null]));
        foreach ($badValues as $bad) {
            $check(is_wp_error($call('update-pro-settings', ['post_type' => 'post', 'expected_hash' => $proState['hash'],
                'settings' => [$field => $bad]])), 'Pro ' . $field . ' rejects an incompatible format');
            $check($call('get-pro-settings', ['post_type' => 'post'])['hash'] === $proState['hash'], 'Malformed Pro patch saves nothing');
        }
    }
    foreach (['metadata_mapping', 'hide_fields'] as $field) {
        foreach ($proState['schema']['properties'][$field]['properties'] as $key => $schema) {
            $bad = $schema['type'] === 'boolean' ? 'false' : false;
            $check(is_wp_error($call('update-pro-settings', ['post_type' => 'post', 'expected_hash' => $proState['hash'],
                'settings' => [$field => [$key => $bad]]])), 'Every nested Pro mapping/checkbox rejects coercion');
        }
    }
}

$postSelection = $call('get-post-workflows', ['post_id' => $postId]);
$workflowBefore = $call('get-workflow', ['id' => $workflowId]);
$changedFlow = (array) $workflowBefore['flow'];
$changedFlow['nodes'][1]['data']['settings']['newStatus']['status'] = 'pending';
try {
    $success($call('save-workflow', ['id' => $workflowId, 'expected_hash' => $workflowBefore['hash'], 'flow' => $changedFlow]), 'Edit the selected workflow without changing its choice label');
    $newSelection = $call('get-post-workflows', ['post_id' => $postId]);
    $check($newSelection['choices'] === $postSelection['choices'] && $newSelection['hash'] !== $postSelection['hash'], 'Graph revisions invalidate a post-workflow hash even when choices are unchanged');
    $stale = $call('run-post-workflow', ['post_id' => $postId, 'workflow_id' => $workflowId,
        'expected_hash' => $postSelection['hash'], 'dry_run' => true]);
    $check(is_wp_error($stale) && ($stale->get_error_data()['status'] ?? 0) === 409, 'An outdated selection cannot run a newly edited workflow');
} finally {
    $current = $call('get-workflow', ['id' => $workflowId]);
    $success($call('save-workflow', ['id' => $workflowId, 'expected_hash' => $current['hash'], 'flow' => (array) $workflowBefore['flow']]), 'Restore the original workflow graph');
}
$postSelection = $call('get-post-workflows', ['post_id' => $postId]);
update_post_meta($postId, '_mcp_comparison_revision', 'changed');
try {
    $stale = $call('run-post-workflow', ['post_id' => $postId, 'workflow_id' => $workflowId,
        'expected_hash' => $postSelection['hash'], 'dry_run' => true]);
    $check(is_wp_error($stale) && ($stale->get_error_data()['status'] ?? 0) === 409, 'Post metadata revisions invalidate manual execution previews');
} finally {
    delete_post_meta($postId, '_mcp_comparison_revision');
}

$restrictedCalls = [
    'manage_options' => [
        ['get-plugin-settings', []], ['get-post-type-settings', ['post_type' => 'post']],
        ['list-post-types', []], ['list-scheduled-actions', []], ['get-debug-status', []], ['export-configuration', []],
    ],
    'edit_publishpress_workflows' => [
        ['get-workflow', ['id' => $workflowId]], ['get-workflow-step-types', []], ['list-workflows', []],
        ['import-workflows', ['workflows' => [['title' => 'Denied import', 'flow' => (array) $workflowBefore['flow']]], 'dry_run' => true]],
    ],
    'publishpress_future_expire_post' => [
        ['get-post-action', ['post_id' => $postId]], ['get-post-workflows', ['post_id' => $postId]],
    ],
];
foreach (['general', 'notifications', 'display', 'admin', 'advanced'] as $group) {
    $state = $call('get-plugin-settings', ['group' => $group]);
    $values = (array) $state['settings'];
    $field = array_key_first($values);
    $restrictedCalls['manage_options'][] = ['update-plugin-settings', ['group' => $group,
        'expected_hash' => $state['hash'], 'settings' => [$field => $values[$field]], 'dry_run' => true]];
}
$state = $call('get-post-type-settings', ['post_type' => 'post']);
$restrictedCalls['manage_options'][] = ['update-post-type-settings', ['post_type' => 'post',
    'expected_hash' => $state['hash'], 'settings' => ['autoEnable' => false], 'dry_run' => true]];
$state = $call('get-post-workflows', ['post_id' => $postId]);
$restrictedCalls['publishpress_future_expire_post'][] = ['update-post-workflows', ['post_id' => $postId,
    'workflow_ids' => $state['workflow_ids'], 'expected_hash' => $state['hash'], 'dry_run' => true]];
$restrictedCalls['publishpress_future_expire_post'][] = ['run-post-workflow', ['post_id' => $postId,
    'workflow_id' => $workflowId, 'expected_hash' => $state['hash'], 'dry_run' => true]];
if ($wordpress->pro()) {
    $state = $call('get-pro-settings', ['post_type' => 'post']);
    $restrictedCalls['manage_options'][] = ['update-pro-settings', ['post_type' => 'post',
        'expected_hash' => $state['hash'], 'settings' => ['hide_metabox' => false], 'dry_run' => true]];
    $restrictedCalls['manage_options'][] = ['get-pro-settings', ['post_type' => 'post']];
    $restrictedCalls['publishpress_future_expire_post'][] = ['get-post-row-actions', ['post_id' => $postId]];
}
foreach ($restrictedCalls as $capability => $calls) {
    $denyOne = static function ($caps) use ($capability) {
        $caps[$capability] = false;
        return $caps;
    };
    add_filter('user_has_cap', $denyOne, 100);
    try {
        foreach ($calls as [$name, $input]) {
            $check(is_wp_error($call($name, $input)), 'Restricted Administrator cannot bypass ' . $capability . ' for ' . $name);
        }
    } finally {
        remove_filter('user_has_cap', $denyOne, 100);
    }
}
foreach (['publish_posts' => ['change-status', 'publish'], 'delete_posts' => ['delete', 'draft'], 'edit_others_posts' => ['stick', 'draft']] as $capability => [$action, $status]) {
    $state = $call('get-post-action', ['post_id' => $postId]);
    $denyOne = static function ($caps) use ($capability) {
        $caps[$capability] = false;
        return $caps;
    };
    add_filter('user_has_cap', $denyOne, 100);
    try {
        $check(!is_wp_error($call('get-post-action', ['post_id' => $postId])), 'Action-specific restrictions preserve authorized schedule reads');
        $check(is_wp_error($call('update-post-action', ['post_id' => $postId, 'expected_hash' => $state['hash'],
            'enabled' => true, 'date' => gmdate('c', time() + 86400), 'action' => $action, 'new_status' => $status, 'dry_run' => true])), 'Scheduling rejects a separately denied ' . $capability);
    } finally {
        remove_filter('user_has_cap', $denyOne, 100);
    }
}

foreach ([$postId, $workflowId] as $deniedId) {
    $denyObject = static function ($caps, $required, $args) use ($deniedId) {
        if (($args[0] ?? '') === 'edit_post' && (int) ($args[2] ?? 0) === $deniedId) {
            foreach ($required as $capability) {
                $caps[$capability] = false;
            }
        }
        return $caps;
    };
    add_filter('user_has_cap', $denyObject, 100, 3);
    try {
        $check(!is_wp_error($call('get-plugin-settings')), 'Object denial does not remove unrelated settings access');
        if ($deniedId === $postId) {
            $check(is_wp_error($call('get-post-action', ['post_id' => $postId])), 'Schedule reads enforce the individual post denial');
            $check(is_wp_error($call('get-post-workflows', ['post_id' => $postId])), 'Workflow selection reads enforce the individual post denial');
            if ($wordpress->pro()) {
                $check(is_wp_error($call('get-post-row-actions', ['post_id' => $postId])), 'Pro row-action discovery enforces the individual post denial');
            }
        } else {
            $check(is_wp_error($call('get-workflow', ['id' => $workflowId])), 'Workflow reads enforce the individual workflow denial');
            $listing = $success($call('list-workflows', ['per_page' => 100]), 'Workflow listing remains available with one inaccessible object');
            $check(!in_array($workflowId, array_column($listing['items'], 'id'), true), 'Workflow listing excludes the denied object');
            $check(is_wp_error($call('export-configuration', ['workflow_ids' => [$workflowId]])), 'Configuration export cannot bypass workflow object permission');
        }
    } finally {
        remove_filter('user_has_cap', $denyObject, 100);
    }
}
