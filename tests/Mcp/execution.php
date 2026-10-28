<?php

/**
 * Two real WordPress requests verify native runner initialization and content effects.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

if (
     PHP_SAPI !== 'cli' || !isset($argv[1], $argv[2]) || !in_array($argv[2], ['setup', 'manual', 'row', 'notice', 'scheduler'], true)
    || !in_array(getenv('FUTURE_MCP_MODE'), ['free', 'pro'], true)
) {
    exit("Usage: FUTURE_MCP_MODE=free|pro php execution.php /isolated/wp-load.php setup|manual|row|notice|scheduler\n");
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
$file = sys_get_temp_dir() . '/publishpress-future-mcp-execution-' . getenv('FUTURE_MCP_MODE') . '.json';
$state = is_file($file) ? json_decode(file_get_contents($file), true) : [];
$call = static function ($name, $input = []) {

    $result = wp_get_ability('publishpress-future/' . $name)->execute($input);
    if (is_wp_error($result)) {
        throw new RuntimeException($name . ': ' . $result->get_error_message());
    }
    return $result;
};
$saveState = static function () use (&$state, $file) {
    file_put_contents($file, wp_json_encode($state));
};
$cleanup = static function () use (&$state, $container, $ids, $wordpress, $file) {

    if (!empty($state['user'])) {
        wp_set_current_user($state['user']);
    }
    foreach (array_reverse($state['posts'] ?? []) as $id) {
        $container->get($ids::EXPIRATION_SCHEDULER)->unschedule($id);
        if ($wordpress->pro()) {
            $proIds = \PublishPress\FuturePro\Core\ServicesAbstract::class;
            $events = $container->get($proIds::DB_TABLE_WORKFLOW_EVENT_DRIVEN_ACTIONS_SCHEMA)->getTableName();
            $logs = $container->get($proIds::DB_TABLE_WORKFLOW_EVENT_DRIVEN_ACTIONS_LOG_SCHEMA)->getTableName();
            $notices = $container->get($proIds::DB_TABLE_WORKFLOW_NOTIFICATIONS_SCHEMA)->getTableName();
            foreach ($GLOBALS['wpdb']->get_col($GLOBALS['wpdb']->prepare("SELECT id FROM %i WHERE workflow_id = %d", $events, $id)) as $eventId) {
                $GLOBALS['wpdb']->delete($logs, ['action_id' => $eventId]);
            }
            $GLOBALS['wpdb']->delete($events, ['workflow_id' => $id]);
            $GLOBALS['wpdb']->delete($notices, ['workflow_id' => $id]);
        }
        wp_delete_post($id, true);
    }
    if (!empty($state['user'])) {
        wp_delete_user($state['user']);
    }
    if (isset($state['options'])) {
        $wordpress->restoreOptions($state['options']);
    }
    if (is_file($file)) {
        unlink($file);
    }
};
try {
    if ($argv[2] === 'setup') {
        if ($state) {
            throw new RuntimeException('An earlier execution fixture needs cleanup first');
        }
        $state = ['posts' => [], 'options' => $wordpress->snapshotOptions(['expirationdateDefaultsPost', 'expirationdateDebug'])];
        $state['user'] = wp_insert_user(['user_login' => 'future_mcp_execution_' . wp_generate_password(8, false), 'user_pass' => wp_generate_password(), 'role' => 'administrator']);
        if (is_wp_error($state['user'])) {
            throw new RuntimeException('Cannot create fixture user');
        }
        $saveState();
        wp_set_current_user($state['user']);
        $wordpress->setDebugEnabled(true);
        $container->get($ids::SETTINGS)->purgeCache();
        $defaults = $call('get-post-type-settings', ['post_type' => 'post']);
        $call('update-post-type-settings', ['post_type' => 'post', 'expected_hash' => $defaults['hash'], 'settings' => ['activeMetaBox' => true, 'autoEnable' => false]]);
        $state['target'] = wp_insert_post(['post_title' => 'Synthetic native execution target', 'post_status' => 'draft', 'post_author' => $state['user']]);
        $state['posts'][] = $state['target'];
        $saveState();
        $definitions = array_column($call('get-workflow-step-types')['steps'], null, 'name');
        $manualName = \PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostWorkflowEnable::getNodeTypeName();
        $changeName = \PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\ChangePostStatus::getNodeTypeName();
        $createFlow = static function ($triggerName, $actionName, $actionSettings, $triggerSettings = []) use ($definitions) {

            return ['nodes' => [
                ['id' => 'trigger1', 'type' => $definitions[$triggerName]['type'], 'position' => ['x' => 0, 'y' => 0],
                    'data' => ['name' => $triggerName, 'slug' => 'manual1', 'settings' => array_merge(['postQuery' => ['postSource' => 'custom', 'postType' => ['post'], 'postId' => [], 'postStatus' => []]], $triggerSettings)]],
                ['id' => 'effect1', 'type' => $definitions[$actionName]['type'], 'position' => ['x' => 0, 'y' => 150],
                    'data' => ['name' => $actionName, 'slug' => 'effect1', 'settings' => $actionSettings]],
            ], 'edges' => [['id' => 'edge1', 'source' => 'trigger1', 'target' => 'effect1', 'sourceHandle' => 'output', 'targetHandle' => 'input']]];
        };
        $flow = $createFlow($manualName, $changeName, ['post' => ['variable' => '{{manual1.post}}'], 'newStatus' => ['status' => 'private']]);
        $workflow = $call('save-workflow', ['title' => 'Native manual execution fixture', 'flow' => $flow, 'status' => 'publish']);
        $state['manual'] = $workflow['id'];
        $state['posts'][] = $workflow['id'];
        $saveState();
        if ($wordpress->pro()) {
            $rowName = \PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostRowAction::getNodeTypeName();
            $rowFlow = $createFlow(
                $rowName,
                $changeName,
                ['post' => ['variable' => '{{manual1.post}}'], 'newStatus' => ['status' => 'private']],
                ['actionLabel' => 'Fixture row action', 'askForConfirmation' => ['enabled' => true, 'message' => ['expression' => 'Run fixture action?']]]
            );
            $row = $call('save-workflow', ['title' => 'Native row execution fixture', 'flow' => $rowFlow, 'status' => 'publish']);
            $state['row'] = $row['id'];
            $state['posts'][] = $row['id'];
            $saveState();
            $noticeName = \PublishPress\Future\Modules\Workflows\Domain\Steps\Actions\Definitions\SendInSiteNotification::getNodeTypeName();
            $noticeFlow = $createFlow($manualName, $noticeName, ['recipient' => ['expression' => (string) $state['user']],
                'subject' => ['expression' => 'Fixture notice'], 'message' => ['expression' => 'Native notification fixture'], 'notificationType' => 'info']);
            $notice = $call('save-workflow', ['title' => 'Native notification execution fixture', 'flow' => $noticeFlow, 'status' => 'publish']);
            $state['notice'] = $notice['id'];
            $state['posts'][] = $notice['id'];
            $saveState();
        }
        echo "Prepared native execution fixtures for a fresh WordPress request.\n";
        exit;
    }
    if ((empty($state['user']) || empty($state['target']) || empty($state['manual']))) {
        throw new RuntimeException('Missing setup state');
    }
    wp_set_current_user($state['user']);
    if ($argv[2] === 'manual') {
        $selection = $call('get-post-workflows', ['post_id' => $state['target']]);
        $selection = $call('update-post-workflows', ['post_id' => $state['target'], 'expected_hash' => $selection['hash'], 'workflow_ids' => [$state['manual']]]);
        $call('run-post-workflow', ['post_id' => $state['target'], 'workflow_id' => $state['manual'], 'expected_hash' => $selection['hash']]);
        if (get_post_status($state['target']) !== 'private') {
            throw new RuntimeException('Native manual runner did not change the post');
        }
        echo "PASS: MCP manual execution runs the native engine and changes post status.\n";
        exit;
    }
    if ($wordpress->pro() && $argv[2] === 'row') {
        wp_update_post(['ID' => $state['target'], 'post_status' => 'draft']);
        $rowActions = $call('get-post-row-actions', ['post_id' => $state['target'], 'per_page' => 100]);
        $rowActions = array_values(array_filter($rowActions['items'], static function ($item) use ($state) {
            return $item['workflow_id'] === $state['row'];
        }));
        if (count($rowActions) !== 1) {
            throw new RuntimeException('Expected one eligible native row action');
        }
        $row = $rowActions[0];
        $request = ['post_id' => $state['target'], 'workflow_id' => $state['row'], 'step_id' => $row['step_id'], 'expected_hash' => $row['hash']];
        $denied = wp_get_ability('publishpress-future/run-post-row-action')->execute($request);
        if (!is_wp_error($denied)) {
            throw new RuntimeException('Native confirmation was bypassed');
        }
        $call('run-post-row-action', $request + ['dry_run' => true]);
        if (get_post_status($state['target']) !== 'draft') {
            throw new RuntimeException('Row action preview changed content');
        }
        $call('run-post-row-action', $request + ['confirmed' => true]);
        if (get_post_status($state['target']) !== 'private') {
            throw new RuntimeException('Native Pro row runner did not change the post');
        }
        echo "PASS: Pro row actions preserve native confirmation, query validation and execution.\n";
        exit;
    }
    if ($wordpress->pro() && $argv[2] === 'notice') {
        $selection = $call('get-post-workflows', ['post_id' => $state['target']]);
        $selection = $call('update-post-workflows', ['post_id' => $state['target'], 'expected_hash' => $selection['hash'], 'workflow_ids' => [$state['notice']]]);
        $call('run-post-workflow', ['post_id' => $state['target'], 'workflow_id' => $state['notice'], 'expected_hash' => $selection['hash']]);
        // Notification steps are asynchronous; execute only this synthetic workflow's own pending actions.
        for ($attempt = 0; $attempt < 3; ++$attempt) {
            $queued = $call('list-scheduled-actions', ['per_page' => 100]);
            $ran = false;
            foreach ($queued['items'] as $queuedAction) {
                if ($queuedAction['action']->workflow_id === $state['notice']) {
                    $call('run-scheduled-action', ['id' => $queuedAction['action']->id, 'expected_hash' => $queuedAction['hash']]);
                    $ran = true;
                }
            }
            if (!$ran) {
                break;
            }
        }
        $notifications = $call('list-notifications', ['per_page' => 100]);
        $notices = array_values(array_filter($notifications['items'], static function ($item) use ($state) {
            return $item['notification']->workflow_id === $state['notice'];
        }));
        if (count($notices) !== 1) {
            throw new RuntimeException('Native workflow did not produce the recipient notification');
        }
        $notice = $notices[0];
        $call('respond-to-notification', ['id' => $notice['notification']->id, 'expected_hash' => $notice['hash'], 'option' => 'dismiss']);
        if (!$call('get-notification', ['id' => $notice['notification']->id])['notification']->is_read) {
            throw new RuntimeException('Native recipient response was not saved');
        }
        echo "PASS: Native Pro workflow produces a notification and accepts the owned response.\n";
        exit;
    }
    $scheduled = $call('get-post-action', ['post_id' => $state['target']]);
    $scheduled = $call('update-post-action', ['post_id' => $state['target'], 'expected_hash' => $scheduled['hash'], 'enabled' => true,
        'date' => gmdate('c', time() + 86400), 'action' => 'change-status', 'new_status' => 'draft']);
    $action = $call('get-scheduled-action', ['id' => $scheduled['action_id']]);
    $call('run-scheduled-action', ['id' => $scheduled['action_id'], 'expected_hash' => $action['hash']]);
    if (get_post_status($state['target']) !== 'draft') {
        throw new RuntimeException('Native scheduler did not run the simple action');
    }
    echo "PASS: MCP run-now executes the native Action Scheduler effect.\n";
} catch (Throwable $error) {
    foreach ($container->get($ids::LOGGER)->fetchLatest(18) as $entry) {
        $entry = (array) $entry;
        echo substr((string) ($entry['message'] ?? ''), 0, 600) . PHP_EOL;
    }
    $cleanup();
    throw $error;
}
$cleanup();
