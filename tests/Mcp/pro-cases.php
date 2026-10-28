<?php

/**
 * Full Pro companion, native workflow step availability and recipient ownership.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

$check($wordpress->pro() && wp_get_ability('publishpress-future/update-pro-settings'), 'Actual Pro activation enables companion settings actions');
register_post_status('mcp_review', ['label' => 'MCP Review', 'public' => true]);
$pro = $success($call('get-pro-settings', ['post_type' => 'post']), 'Read typed Pro settings without licensing data');
$check(!isset($pro['settings']->license_key, $pro['schema']['properties']['license_key']), 'Pro discovery excludes licensing credentials');
$patch = ['base_date' => 'publishing', 'custom_statuses' => ['mcp_review'], 'metadata_mapping_enabled' => true,
    'hide_metabox' => true, 'metadata_mapping' => ['_expiration-date' => 'mcp_fixture_expiry'], 'hide_fields' => ['_expiration-date' => true]];
$preview = $success($call('update-pro-settings', ['post_type' => 'post', 'expected_hash' => $pro['hash'], 'settings' => $patch, 'dry_run' => true]), 'Preview all Pro settings shapes');
$check($call('get-pro-settings', ['post_type' => 'post'])['hash'] === $pro['hash'], 'Pro settings preview preserves storage');
$updatedPro = $success($call('update-pro-settings', ['post_type' => 'post', 'expected_hash' => $pro['hash'], 'settings' => $patch]), 'Save native Pro settings');
$check($updatedPro['settings']->base_date === 'publishing' && $updatedPro['settings']->custom_statuses === ['mcp_review']
    && $updatedPro['settings']->metadata_mapping->{'_expiration-date'} === 'mcp_fixture_expiry', 'Pro values round-trip using native storage');
$check(is_wp_error($call('update-pro-settings', ['post_type' => 'post', 'expected_hash' => $pro['hash'], 'settings' => $patch])), 'Pro settings reject stale revisions');
$nativeMappingKeys = [
    \PublishPress\Future\Modules\Expirator\PostMetaAbstract::EXPIRATION_TIMESTAMP,
    \PublishPress\Future\Modules\Expirator\PostMetaAbstract::EXPIRATION_TYPE,
    \PublishPress\Future\Modules\Expirator\PostMetaAbstract::EXPIRATION_POST_STATUS,
    \PublishPress\Future\Modules\Expirator\PostMetaAbstract::EXPIRATION_STATUS,
    \PublishPress\Future\Modules\Expirator\PostMetaAbstract::EXPIRATION_TAXONOMY,
    \PublishPress\Future\Modules\Expirator\PostMetaAbstract::EXPIRATION_TERMS,
];
$check(array_keys($updatedPro['schema']['properties']['metadata_mapping']['properties']) === $nativeMappingKeys, 'Pro discovery includes all six native metadata mapping fields');
foreach ($nativeMappingKeys as $key) {
    foreach (['mcp.fixture:' . $key, ''] as $mapped) {
        $beforeMap = (array) $updatedPro['settings']->metadata_mapping;
        $input = ['post_type' => 'post', 'expected_hash' => $updatedPro['hash'],
            'settings' => ['metadata_mapping' => [$key => $mapped], 'hide_fields' => [$key => $mapped !== '']]];
        $preview = $success($call('update-pro-settings', $input + ['dry_run' => true]), 'Preview native Pro mapping ' . $key);
        $check($call('get-pro-settings', ['post_type' => 'post'])['hash'] === $updatedPro['hash'], 'Pro mapping preview saves nothing');
        $updatedPro = $success($call('update-pro-settings', $input), 'Save native Pro mapping ' . $key);
        $check($updatedPro['settings']->metadata_mapping->$key === $mapped
            && $updatedPro['settings']->hide_fields->$key === ($mapped !== ''), 'Mapping and hidden checkbox round-trip with exact native values');
        $check(array_diff_key((array) $updatedPro['settings']->metadata_mapping, [$key => true]) === array_diff_key($beforeMap, [$key => true]), 'Nested mapping patches preserve other entries');
        $nativeStored = get_option(\PublishPress\FuturePro\Models\SettingsModel::OPTION_METADATA_MAPPING);
        $check($nativeStored['post'][$key] === $mapped, 'Native Pro option contains the requested mapping');
    }
}
$choices = $success($call('get-post-action-choices', ['post_id' => $postId]), 'Discover active Pro custom statuses');
$check(in_array('mcp_review', array_column($choices['statuses'], 'value'), true), 'Scheduling choices include only enabled Pro custom statuses');
$check(is_wp_error($call('update-pro-settings', ['post_type' => 'post', 'expected_hash' => $updatedPro['hash'], 'settings' => ['license_key' => 'never-accepted']])), 'Pro rejects unknown and secret settings');
$check(is_wp_error($call('update-pro-settings', ['post_type' => 'post', 'expected_hash' => $updatedPro['hash'], 'settings' => ['hide_metabox' => 'true']])), 'Pro settings reject scalar coercion');
$check(is_wp_error($call('update-pro-settings', ['post_type' => 'post', 'expected_hash' => $updatedPro['hash'], 'settings' => ['custom_statuses' => ['not-a-status']]])), 'Pro settings reject nonexistent custom statuses');
$check(count(array_filter($graphTypes['steps'], static function ($step) {
    return $step['isProFeature'] && $step['available'];
})) > 0, 'Native Pro workflow steps are discoverable and available');
$proIds = \PublishPress\FuturePro\Core\ServicesAbstract::class;
foreach (
    [$proIds::DB_TABLE_WORKFLOW_NOTIFICATIONS_SCHEMA, $proIds::DB_TABLE_WORKFLOW_EVENT_DRIVEN_ACTIONS_SCHEMA,
    $proIds::DB_TABLE_WORKFLOW_EVENT_DRIVEN_ACTIONS_LOG_SCHEMA] as $schemaId
) {
    $container->get($schemaId)->createTable();
}
$notificationModel = new \PublishPress\FuturePro\Modules\Notifications\Models\NotificationModel();
$notificationModel->userId = $users['administrator'];
$notificationModel->workflowId = $workflowId;
$notificationModel->stepId = 'action1';
$notificationModel->message = 'Synthetic recipient-only message';
$notificationModel->type = 'info';
$notificationId = $notificationModel->create(['options' => [
    ['name' => 'dismiss', 'label' => 'Dismiss'],
    ['name' => 'ApproveCase', 'label' => 'Approve case'],
]]);
$check((bool) $notificationId, 'Native notification model creates the recipient fixture');
try {
    $notice = $success($call('get-notification', ['id' => $notificationId]), 'Read notification owned by authenticated recipient');
    $check(in_array('dismiss', array_column($notice['notification']->options, 'name'), true), 'Notification discovery returns offered native options');
    $success($call('respond-to-notification', ['id' => $notificationId, 'expected_hash' => $notice['hash'], 'option' => 'ApproveCase', 'dry_run' => true]), 'Preview accepts the exact native offered option name without an invented lowercase restriction');
    $success($call('respond-to-notification', ['id' => $notificationId, 'expected_hash' => $notice['hash'], 'option' => 'dismiss', 'dry_run' => true]), 'Preview native notification response');
    $check(!$call('get-notification', ['id' => $notificationId])['notification']->is_read, 'Notification preview does not mark read or execute steps');
    $check(is_wp_error($call('respond-to-notification', ['id' => $notificationId, 'expected_hash' => $notice['hash'], 'option' => 'not-offered'])), 'Unoffered notification options are rejected');
    wp_set_current_user($users['subscriber']);
    $check(is_wp_error($call('get-notification', ['id' => $notificationId])), 'Another recipient cannot read the notification');
    $check(is_wp_error($call('respond-to-notification', ['id' => $notificationId, 'expected_hash' => $notice['hash'], 'option' => 'dismiss'])), 'Another recipient cannot respond even with its ID and hash');
    $myNotices = $success($call('list-notifications'), 'Read only the current recipient notification page');
    $check(!$myNotices['items'], 'Recipient listing never leaks another user\'s message');
} finally {
    wp_set_current_user($users['administrator']);
    $GLOBALS['wpdb']->delete($container->get($proIds::DB_TABLE_WORKFLOW_NOTIFICATIONS_SCHEMA)->getTableName(), ['id' => $notificationId]);
    $GLOBALS['wpdb']->delete($container->get($proIds::DB_TABLE_WORKFLOW_EVENT_DRIVEN_ACTIONS_SCHEMA)->getTableName(), ['id' => $notificationModel->actionId]);
    $GLOBALS['wpdb']->delete($container->get($proIds::DB_TABLE_WORKFLOW_EVENT_DRIVEN_ACTIONS_LOG_SCHEMA)->getTableName(), ['action_id' => $notificationModel->actionId]);
}
