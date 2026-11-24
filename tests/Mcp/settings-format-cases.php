<?php

/**
 * Every discovered setting must survive the native setter/getter and strict transport.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

$lateRole = 'mcp_late_role_fixture';
$roleState = $success($call('get-plugin-settings', ['group' => 'general']), 'Initial role settings');
add_role($lateRole, 'Late role fixture', ['read' => true]);
try {
    $roles = array_values(array_unique(array_merge($roleState['settings']->allowUserRoles, [$lateRole])));
    $input = ['group' => 'general', 'expected_hash' => $roleState['hash'], 'settings' => ['allowUserRoles' => $roles]];
    $success($call('update-plugin-settings', $input + ['dry_run' => true]), 'Preview a role registered after abilities');
    $check($call('get-plugin-settings', ['group' => 'general'])['hash'] === $roleState['hash'], 'Late-role preview preserves settings');
    $savedRole = $success($call('update-plugin-settings', $input), 'Save a late native role through Adapter');
    $check(in_array($lateRole, $savedRole['settings']->allowUserRoles, true), 'Late role survives native read-back');
    $check(is_wp_error($call('update-plugin-settings', ['group' => 'general', 'expected_hash' => $savedRole['hash'],
        'settings' => ['allowUserRoles' => ['not-a-native-role'], 'hideCalendarByDefault' => true]])), 'Unknown mixed role patch is rejected');
    $check($call('get-plugin-settings', ['group' => 'general'])['hash'] === $savedRole['hash'], 'Rejected role patch writes nothing');
} finally {
    $freshRole = $call('get-plugin-settings', ['group' => 'general']);
    $call('update-plugin-settings', ['group' => 'general', 'expected_hash' => $freshRole['hash'],
        'settings' => ['allowUserRoles' => $roleState['settings']->allowUserRoles]]);
    remove_role($lateRole);
}

foreach (['general', 'notifications', 'display', 'admin', 'advanced'] as $group) {
    $state = $call('get-plugin-settings', ['group' => $group]);
    foreach ($state['schema']['properties'] as $field => $schema) {
        $value = $state['settings']->$field;
        $input = ['group' => $group, 'expected_hash' => $state['hash'], 'settings' => [$field => $value]];
        $preview = $success($call('update-plugin-settings', $input + ['dry_run' => true]), $group . '/' . $field . ' native-value preview');
        $saved = $success($call('update-plugin-settings', $input), $group . '/' . $field . ' native-value save');
        $check($saved['settings']->$field === $preview['settings']->$field, $field . ' preview and native read-back agree');
        $state = $saved;
        $invalid = $schema['type'] === 'boolean' ? ['true', 'false', 0, 1, null]
            : ($schema['type'] === 'integer' ? ['45', true, 1.5, null]
            : ($schema['type'] === 'array' ? [['invalid-key' => 'subscriber'], [true], [123], 'subscriber', null] : [123, false, [], null]));
        foreach ($invalid as $bad) {
            $beforeHash = $state['hash'];
            $check(is_wp_error($call('update-plugin-settings', ['group' => $group, 'expected_hash' => $beforeHash, 'settings' => [$field => $bad]])), $field . ' rejects malformed JSON types');
            $check($call('get-plugin-settings', ['group' => $group])['hash'] === $beforeHash, $field . ' malformed patch saves nothing');
        }
    }
}
foreach (['emailNotificationAddressesList', 'pastDueActionsNotificationList'] as $field) {
    $state = $call('get-plugin-settings', ['group' => 'notifications']);
    $saved = $success($call('update-plugin-settings', ['group' => 'notifications', 'expected_hash' => $state['hash'], 'settings' => [$field => []]]), $field . ' accepts empty native lists');
    $check($saved['settings']->$field === [], $field . ' clears without becoming an object');
}
