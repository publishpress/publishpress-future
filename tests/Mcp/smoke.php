<?php

/**
 * Full native bootstrap regression suite; run only on an isolated mcp_smoke_ database.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

if (PHP_SAPI !== 'cli' || !isset($argv[1]) || !in_array(getenv('FUTURE_MCP_MODE'), ['free', 'pro'], true)) {
    exit("Usage: FUTURE_MCP_MODE=free|pro php smoke.php /isolated/wp-load.php [adapter-directory]\n");
}
require $argv[1];
if ($GLOBALS['wpdb']->prefix !== 'mcp_smoke_') {
    exit("Use an isolated mcp_smoke_ database.\n");
}
require_once ABSPATH . 'wp-admin/includes/user.php';
$container = \PublishPress\Future\Core\DI\Container::getInstance();
$ids = \PublishPress\Future\Core\DI\ServicesAbstract::class;
$wordpress = $container->get($ids::MCP_WORDPRESS);
if ($wordpress->pro() !== (getenv('FUTURE_MCP_MODE') === 'pro')) {
    throw new RuntimeException('The requested test edition must match the actual loaded Free or Pro distribution.');
}
$settingsProvider = $container->get($ids::MCP_SETTINGS);
$optionKeys = ['timezone_string', 'gmt_offset', 'expirationdateDefaultsPost', 'expirationdateDefaultsPage'];
foreach (['general', 'notifications', 'display', 'admin', 'advanced'] as $group) {
    $optionKeys = array_merge($optionKeys, $settingsProvider->optionKeys($group));
}
if ($wordpress->pro()) {
    $optionKeys = array_merge($optionKeys, ['ppfuturepro_base_date', 'ppfuturepro_enabled_custom_statuses', 'ppfuturepro_metadata_mapping_status',
        'ppfuturepro_hide_metabox', 'ppfuturepro_metadata_mapping', 'ppfuturepro_hide_fields']);
}
$snapshot = $wordpress->snapshotOptions(array_unique($optionKeys));
$posts = $users = $terms = [];
$checks = 0;
$check = static function ($condition, $message) use (&$checks) {

    if (!$condition) {
        throw new RuntimeException($message);
    }
    ++$checks;
    echo "PASS: $message\n";
};
$call = static function ($name, $input = []) {

    $GLOBALS['wpdb']->last_error = '';
    $result = wp_get_ability('publishpress-future/' . $name)->execute($input);
    if ($GLOBALS['wpdb']->last_error !== '') {
        throw new RuntimeException($name . ' produced an unexpected database error');
    }
    return $result;
};
$success = static function ($value, $message) use ($check) {

    $check(!is_wp_error($value), $message . (is_wp_error($value) ? ': ' . $value->get_error_message() : ''));
    return $value;
};
try {
    foreach (['administrator', 'editor', 'subscriber'] as $role) {
        $id = wp_insert_user(['user_login' => 'future_mcp_' . $role . wp_generate_password(8, false), 'user_pass' => wp_generate_password(), 'role' => $role]);
        if (is_wp_error($id)) {
                throw new RuntimeException('Cannot create fixture user');
        }
        $users[$role] = $id;
    }
    wp_set_current_user($users['administrator']);
    $check(current_user_can('publishpress_future_expire_post') && current_user_can('edit_publishpress_workflows'), 'Native installer grants Future capabilities');
    $abilities = array_filter(wp_get_abilities(), static function ($ability) {
        return strpos($ability->get_name(), 'publishpress-future/') === 0;
    });
    $check(count($abilities) >= 25, 'Full bootstrap registers Future actions');
    $features = $success($call('list-features'), 'Discover every Future feature scope');
    $check(count($features['features']) === 11, 'Feature inventory covers native Free and Pro scopes');
    foreach ($features['features'] as $feature) {
        $check($feature['available'] === (!$feature['requires_pro'] || $wordpress->pro()), 'Feature availability respects the actual edition');
        if ($feature['available']) {
            foreach (array_merge($feature['read_actions'], $feature['write_actions']) as $name) {
                $check((bool) wp_get_ability('publishpress-future/' . $name), 'Feature route exists: ' . $name);
            }
        }
    }

    foreach ($abilities as $ability) {
        $meta = $ability->get_meta();
        $check($ability->get_category() === 'publishpress' && !$meta['public'] && !$meta['show_in_rest'] && !$meta['mcp']['public'], $ability->get_name() . ' is private');
    }
    $check(is_wp_error($call('get-workflow-step-types', ['unknown' => true])), 'Unknown empty-input properties are rejected');
    foreach (['general', 'notifications', 'display', 'admin', 'advanced'] as $group) {
        $state = $success($call('get-plugin-settings', ['group' => $group]), 'Read native ' . $group . ' settings');
        $check(!is_wp_error(rest_validate_value_from_schema((array) $state['settings'], $state['schema'])), $group . ' saved values match discovered types');
        $patch = (array) $state['settings'];
        if ($group === 'general') {
                $patch['defaultDateTimeOffset'] = '+9 days';
        }
        if ($group === 'notifications') {
            $patch['enableEmailNotification'] = false;
            $patch['emailNotificationAddressesList'] = ['fixture@example.org'];
        }
        if ($group === 'display') {
            $patch['footerContents'] = '<em>Fixture footer</em>';
            $patch['footerStyle'] = 'color: pink;';
        }
        if ($group === 'admin') {
            $patch['columnStyle'] = 'simple';
            $patch['timeFormatForDatePicker'] = '24h';
        }
        if ($group === 'advanced') {
            $patch['scheduledWorkflowStepsCleanupRetention'] = 45;
        }
        $input = ['group' => $group, 'expected_hash' => $state['hash'], 'settings' => $patch];
        $preview = $success($call('update-plugin-settings', $input + ['dry_run' => true]), 'Preview ' . $group . ' settings');
        $check($call('get-plugin-settings', ['group' => $group])['hash'] === $state['hash'], $group . ' preview saves nothing');
        $saved = $success($call('update-plugin-settings', $input), 'Save native ' . $group . ' settings');
        $check((array) $saved['settings'] == (array) $preview['settings'], $group . ' native values round-trip');
        if ($state['hash'] !== $saved['hash']) {
            $check(is_wp_error($call('update-plugin-settings', $input)), $group . ' stale hash is rejected');
        }
    }
    require __DIR__ . '/settings-format-cases.php';
    $state = $call('get-plugin-settings', ['group' => 'general']);
    $check(is_wp_error($call('update-plugin-settings', ['group' => 'general', 'expected_hash' => $state['hash'], 'settings' => ['hideCalendarByDefault' => 'true']])), 'String booleans are rejected');
    $check(is_wp_error($call('update-plugin-settings', ['group' => 'general', 'expected_hash' => $state['hash'], 'settings' => ['license_key' => 'not-accepted']])), 'Secret and unknown settings are rejected');
    $types = $success($call('list-post-types'), 'Discover native supported post types');
    $check(in_array('post', array_column($types['items'], 'name'), true), 'Native post type is available');
    $defaults = $success($call('get-post-type-settings', ['post_type' => 'post']), 'Read post-type defaults');
    $preview = $success($call('update-post-type-settings', ['post_type' => 'post', 'expected_hash' => $defaults['hash'], 'settings' => ['activeMetaBox' => true, 'autoEnable' => false], 'dry_run' => true]), 'Preview post-type defaults');
    $check($call('get-post-type-settings', ['post_type' => 'post'])['hash'] === $defaults['hash'], 'Post-type preview saves nothing');
    $success($call('update-post-type-settings', ['post_type' => 'post', 'expected_hash' => $defaults['hash'], 'settings' => ['activeMetaBox' => true, 'autoEnable' => false]]), 'Enable native scheduling defaults');
    $posts[] = $postId = wp_insert_post(['post_title' => 'MCP synthetic schedule', 'post_status' => 'draft', 'post_author' => $users['administrator']]);
    $postAction = $success($call('get-post-action', ['post_id' => $postId]), 'Read unscheduled post action');
    $check(!$postAction['settings']->enabled && $postAction['settings']->timestamp === 0, 'Unscheduled reads have stable empty dates');
    $future = time() + 86400 * 3;
    foreach (['UTC', 'America/New_York', 'Asia/Kathmandu'] as $timezone) {
        update_option('timezone_string', $timezone);
        $before = $call('get-post-action', ['post_id' => $postId]);
        $input = ['post_id' => $postId, 'expected_hash' => $before['hash'], 'enabled' => true, 'date' => gmdate('c', $future), 'action' => 'change-status', 'new_status' => 'draft'];
        $preview = $success($call('update-post-action', $input + ['dry_run' => true]), 'Preview schedule in ' . $timezone);
        $check($call('get-post-action', ['post_id' => $postId])['hash'] === $before['hash'], 'Schedule preview preserves storage');
        $saved = $success($call('update-post-action', $input), 'Save native schedule in ' . $timezone);
        $check($saved['settings']->timestamp === $future && $saved['action_id'] > 0, 'Native scheduler retains exact UTC time');
        $check(is_wp_error($call('update-post-action', $input)), 'Schedule stale hash is rejected');
        $runtime = $success($call('get-scheduled-action', ['id' => $saved['action_id']]), 'Read only Future scheduler metadata');
        $check($runtime['action']->post_id === $postId && !isset($runtime['action']->args), 'Runtime output excludes execution arguments');
        $success($call('cancel-scheduled-action', ['id' => $saved['action_id'], 'expected_hash' => $runtime['hash']]), 'Cancel through the native post scheduler');
        $check(!$call('get-post-action', ['post_id' => $postId])['settings']->enabled, 'Cancellation disables native post metadata');
    }
    $timezoneBeforeFold = get_option('timezone_string');
    update_option('timezone_string', 'America/New_York');
    $foldState = $call('get-post-action', ['post_id' => $postId]);
    foreach (['2030-11-03T01:30:00-04:00', '2030-11-03T01:30:00-05:00'] as $date) {
        $instant = (new DateTimeImmutable($date))->getTimestamp();
        $result = $call('update-post-action', ['post_id' => $postId, 'expected_hash' => $foldState['hash'],
            'enabled' => true, 'date' => $date, 'action' => 'change-status', 'new_status' => 'draft', 'dry_run' => true]);
        $check($wordpress->canRepresentSchedule($instant) ? !is_wp_error($result) : is_wp_error($result), 'DST fold preview agrees with native scheduler representation');
    }
    $check(is_wp_error($call('update-post-action', ['post_id' => $postId, 'expected_hash' => $foldState['hash'],
        'enabled' => true, 'date' => '2030-02-31T12:00:00Z', 'action' => 'change-status', 'new_status' => 'draft'])), 'Invalid calendar dates cannot be silently normalized');
    $check($call('get-post-action', ['post_id' => $postId])['hash'] === $foldState['hash'], 'DST and invalid-date checks do not save schedules');
    update_option('timezone_string', $timezoneBeforeFold);
    $graphTypes = $success($call('get-workflow-step-types'), 'Discover native PHP workflow step catalogue');
    $definitions = array_column($graphTypes['steps'], null, 'name');
    foreach ($definitions as $definition) {
        $nativeSettings = [];
        foreach ($definition['settingsSchema'] as $panel) {
            foreach ($panel['fields'] ?? [] as $field) {
                if (array_key_exists('default', $field)) {
                    $nativeSettings[$field['name']] = $field['default'];
                }
            }
        }
        $nativeGraph = ['nodes' => [['id' => 'native1', 'type' => $definition['type'], 'position' => ['x' => 0, 'y' => 0],
            'data' => ['name' => $definition['name'], 'slug' => 'native1', 'settings' => $nativeSettings]]], 'edges' => []];
        $nativePreview = $call('save-workflow', ['title' => 'Native default schema fixture', 'flow' => $nativeGraph, 'dry_run' => true]);
        if ($definition['available'] && $definition['name'] !== 'advanced/do.action') {
            $success($nativePreview, $definition['name'] . ' accepts its native default field shapes');
        } elseif (!$definition['available']) {
            $check(is_wp_error($nativePreview), $definition['name'] . ' honors the inactive edition gate');
        }
    }
    $triggerName = \PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostWorkflowEnable::getNodeTypeName();
    $statusName = \PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\ChangePostStatus::getNodeTypeName();
    $trigger = $definitions[$triggerName];
    $action = $definitions[$statusName];
    $flow = ['nodes' => [
        ['id' => 'trigger1', 'type' => $trigger['type'], 'position' => ['x' => 10, 'y' => 20], 'data' => ['name' => $triggerName, 'slug' => 'manual1', 'settings' => ['postQuery' => ['postSource' => 'custom', 'postType' => ['post'], 'postId' => [], 'postStatus' => []]]]],
        ['id' => 'action1', 'type' => $action['type'], 'position' => ['x' => 50, 'y' => 200], 'data' => ['name' => $statusName, 'slug' => 'status1', 'settings' => ['post' => ['variable' => '{{manual1.post}}'], 'newStatus' => ['status' => 'private']]]],
    ], 'edges' => [['id' => 'edge1', 'source' => 'trigger1', 'target' => 'action1', 'sourceHandle' => 'output', 'targetHandle' => 'input']], 'viewport' => ['x' => 12, 'y' => 34, 'zoom' => 0.8]];
    $preview = $success($call('save-workflow', ['title' => 'Synthetic MCP workflow', 'flow' => $flow, 'dry_run' => true]), 'Preview native workflow graph');
    $check($preview['id'] === 0 && $preview['dry_run'], 'Workflow preview creates no post');
    $workflow = $success($call('save-workflow', ['title' => 'Synthetic MCP workflow', 'flow' => $flow]), 'Create disabled native workflow');
    $posts[] = $workflowId = $workflow['id'];
    $check($workflow['status'] === 'draft' && (array) $workflow['flow']->viewport === $flow['viewport'], 'Native editor viewport and draft status round-trip');
    $patched = $success($call('update-workflow-step', ['id' => $workflowId, 'expected_hash' => $workflow['hash'], 'node_id' => 'action1', 'settings' => ['newStatus' => ['status' => 'draft']]]), 'Patch one native workflow step');
    $check($patched['flow']->edges === $workflow['flow']->edges && $patched['flow']->viewport === $workflow['flow']->viewport, 'Step patch preserves connections and editor state');
    $check(is_wp_error($call('save-workflow', ['id' => $workflowId, 'expected_hash' => $workflow['hash'], 'title' => 'Stale'])), 'Workflow stale hash is rejected');
    $bad = $flow;
    $bad['edges'][0]['target'] = 'missing';
    $check(is_wp_error($call('save-workflow', ['title' => 'Invalid graph', 'flow' => $bad])), 'Dangling edges are rejected before creation');
    $bad = $flow;
    $bad['nodes'][1]['data']['settings']['newStatus'] = 'private';
    $check(is_wp_error($call('save-workflow', ['title' => 'Invalid type', 'flow' => $bad])), 'Native post-status object cannot be replaced with a string');
    $published = $success($call('save-workflow', ['id' => $workflowId, 'expected_hash' => $patched['hash'], 'status' => 'publish']), 'Enable native workflow');
    $postWorkflows = $success($call('get-post-workflows', ['post_id' => $postId]), 'Read eligible manual workflow selection');
    $check(in_array($workflowId, array_map('intval', array_column($postWorkflows['choices'], 'workflowId')), true), 'Native post query recognizes the enabled workflow');
    $selected = $success($call('update-post-workflows', ['post_id' => $postId, 'expected_hash' => $postWorkflows['hash'], 'workflow_ids' => [$workflowId]]), 'Select eligible manual workflow without running it');
    $check(get_post_status($postId) === 'draft', 'Selecting a workflow does not execute steps');
    $success($call('run-post-workflow', ['post_id' => $postId, 'workflow_id' => $workflowId, 'expected_hash' => $selected['hash'], 'dry_run' => true]), 'Preview manual workflow execution');
    $check(get_post_status($postId) === 'draft', 'Execution preview changes no content');
    $export = $success($call('export-configuration', ['workflow_ids' => [$workflowId], 'groups' => ['general', 'admin'], 'post_types' => ['post']]), 'Export explicit native configuration scopes');
    $check(!isset($export['configuration']->settings->license_key), 'Export excludes licensing credentials');
    $imported = $success($call('import-workflows', ['workflows' => $export['configuration']->workflows, 'dry_run' => true]), 'Preview import of native backup workflow records');
    $check($imported['workflows'][0]['status'] === 'draft' && $imported['workflows'][0]['id'] === 0, 'Workflow import preview ignores source status and ID');
    $imported = $success($call('import-workflows', ['workflows' => $export['configuration']->workflows]), 'Import workflow as a new disabled draft');
    $posts[] = $imported['workflows'][0]['id'];
    $check($imported['workflows'][0]['id'] !== $workflowId && get_post_status($imported['workflows'][0]['id']) === 'draft', 'Imported workflow never overwrites or enables source');
    $duplicateSource = $call('get-workflow', ['id' => $workflowId]);
    $copy = $success($call('duplicate-workflow', ['id' => $workflowId, 'expected_hash' => $duplicateSource['hash']]), 'Duplicate a native workflow into a disabled draft');
    $posts[] = $copy['id'];
    $check($copy['id'] !== $workflowId && $copy['status'] === 'draft' && $copy['flow'] == $duplicateSource['flow'], 'Workflow duplication preserves the graph without enabling or overwriting the source');
    $success($call('delete-workflow', ['id' => $copy['id'], 'expected_hash' => $copy['hash'], 'dry_run' => true]), 'Preview native workflow Trash');
    $check(get_post_status($copy['id']) === 'draft', 'Trash preview preserves the workflow');
    $success($call('delete-workflow', ['id' => $copy['id'], 'expected_hash' => $copy['hash']]), 'Trash a verified workflow using native permissions');
    $check(get_post_status($copy['id']) === 'trash' && get_post_status($workflowId) === 'publish', 'Trash preserves the original enabled workflow');
    $success($call('get-debug-status'), 'Read bounded native diagnostics');
    $success($call('list-scheduled-actions'), 'List only scoped Future actions');
    require __DIR__ . '/failure-cases.php';
    if ($wordpress->pro()) {
        require __DIR__ . '/pro-cases.php';
    }
    require __DIR__ . '/comparison-cases.php';
    foreach (['subscriber', 'editor'] as $role) {
        wp_set_current_user($users[$role]);
        $check(is_wp_error($call('get-plugin-settings')), $role . ' cannot read administrative settings');
        $check(is_wp_error($call('get-workflow-step-types')), $role . ' cannot administer workflows without capability');
        $check(is_wp_error($call('get-post-action', ['post_id' => $postId])), $role . ' cannot edit another user\'s schedule without native object permissions');
    }
    wp_set_current_user($users['administrator']);
    $deny = static function ($caps, $required) {

        foreach (['manage_options', 'edit_publishpress_workflows', 'publishpress_future_expire_post'] as $cap) {
            $caps[$cap] = false;
        }
        return $caps;
    };
    add_filter('user_has_cap', $deny, 100, 2);
    try {
        $check(is_wp_error($call('get-plugin-settings')), 'Administrator role cannot bypass denied settings capability');
        $check(is_wp_error($call('get-workflow', ['id' => $workflowId])), 'Administrator role cannot bypass denied workflow capability');
        $check(is_wp_error($call('get-post-action', ['post_id' => $postId])), 'Administrator role cannot bypass denied scheduling capability');
    } finally {
        remove_filter('user_has_cap', $deny, 100);
    }
    if (isset($argv[2])) {
        require_once $argv[2] . '/mcp-adapter.php';
        $check(strpos(wp_json_encode(\WP\MCP\Abilities\DiscoverAbilitiesAbility::execute([])), 'publishpress-future/') === false, 'Default server discovery excludes Future abilities');
        foreach ($abilities as $ability) {
            $tool = \WP\MCP\Domain\Tools\McpTool::fromAbility($ability);
            $check(!is_wp_error($tool), 'Released adapter projects ' . $ability->get_name());
            $check(is_wp_error(\WP\MCP\Abilities\ExecuteAbilityAbility::check_permission(['ability_name' => $ability->get_name(), 'parameters' => []])), 'Default server denies ' . $ability->get_name());
        }
        $tool = \WP\MCP\Domain\Tools\McpTool::fromAbility(wp_get_ability('publishpress-future/get-plugin-settings'));
        $check(!is_wp_error($tool->execute(['group' => 'admin'])), 'Custom server executes using the native authenticated identity');
        $settingsTool = \WP\MCP\Domain\Tools\McpTool::fromAbility(wp_get_ability('publishpress-future/update-plugin-settings'));
        foreach (['general' => ['allowUserRoles' => []], 'notifications' => ['emailNotificationAddressesList' => []]] as $group => $patch) {
            $state = $call('get-plugin-settings', ['group' => $group]);
            $check(!is_wp_error($settingsTool->execute(['group' => $group, 'expected_hash' => $state['hash'], 'settings' => $patch, 'dry_run' => true])), 'Released adapter accepts ' . $group . ' empty native lists');
        }
    }
    echo "Completed $checks Future checks in " . getenv('FUTURE_MCP_MODE') . " mode.\n";
} finally {
    wp_set_current_user($users['administrator'] ?? 0);
    foreach (array_reverse($posts) as $id) {
        $container->get($ids::EXPIRATION_SCHEDULER)->unschedule($id);
        wp_delete_post($id, true);
    }
    foreach ($terms as $id) {
        wp_delete_term($id, 'category');
    }
    foreach ($users as $id) {
        wp_delete_user($id);
    }
    $wordpress->restoreOptions($snapshot);
    $container->get($ids::SETTINGS)->purgeCache();
}
