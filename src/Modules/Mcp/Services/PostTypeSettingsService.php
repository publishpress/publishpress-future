<?php

/**
 * Typed settings for the native Post Types screen.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp\Services;

use PublishPress\Future\Framework\WordPress\Facade\McpFacade;
use PublishPress\Future\Modules\Expirator\Models\ExpirationActionsModel;
use PublishPress\Future\Modules\Mcp\Registry;
use PublishPress\Future\Modules\Settings\Models\SettingsPostTypesModel;
use PublishPress\Future\Modules\Settings\SettingsFacade;

\defined('ABSPATH') || exit;

class PostTypeSettingsService
{
    private McpFacade $wordpress;
    private SettingsFacade $settings;
    private SettingsPostTypesModel $types;
    private ExpirationActionsModel $actions;

    /** @since 4.12.0 */
    public function __construct(McpFacade $wordpress, SettingsFacade $settings, SettingsPostTypesModel $types, ExpirationActionsModel $actions)
    {
        $this->wordpress = $wordpress;
        $this->settings = $settings;
        $this->types = $types;
        $this->actions = $actions;
    }

    /** @since 4.12.0 */
    public function canManage(): bool
    {
        return $this->wordpress->can('manage_options');
    }

    /** @since 4.12.0 */
    public function register(Registry $registry): void
    {
        $target = ['type' => 'string', 'maxLength' => 100];
        $output = ['post_type' => ['type' => 'string'], 'settings' => ['type' => 'object'], 'schema' => ['type' => 'object'],
            'choices' => ['type' => 'object'], 'hash' => ['type' => 'string'], 'dry_run' => ['type' => 'boolean']];
        $registry->add(
            'list-post-types',
            __('Discover Future post types', 'post-expirator'),
            __('Read post types supported by the native Post Types settings screen, with names, labels and enabled state. Requires manage_options; does not expose content.', 'post-expirator'),
            Registry::object(),
            ['items' => ['type' => 'array', 'items' => ['type' => 'object']]],
            [$this, 'listing'],
            [$this, 'canManage']
        );
        $registry->add(
            'get-post-type-settings',
            __('Read Future post-type defaults', 'post-expirator'),
            __('Read Post Types settings, native field names, action/status/taxonomy choices and latest hash. These are defaults, not the saved action of a particular post. Custom statuses are supplied by active Pro.', 'post-expirator'),
            Registry::object(['post_type' => $target], ['post_type']),
            $output,
            [$this, 'get'],
            [$this, 'canManage']
        );
        $registry->add(
            'update-post-type-settings',
            __('Update Future post-type defaults', 'post-expirator'),
            __('Patch supplied Post Types defaults using the latest hash. Supports enabling the metabox, automatic actions, native date offsets, actions, statuses, terms and notification addresses. Preserves omitted fields and other post types; does not reschedule existing posts. dry_run validates only. Pro metadata mapping has separate actions.', 'post-expirator'),
            Registry::object(['post_type' => $target, 'settings' => ['type' => 'object', 'minProperties' => 1, 'maxProperties' => 20],
                'expected_hash' => Registry::revision(), 'dry_run' => Registry::preview()], ['post_type', 'settings', 'expected_hash']),
            $output,
            [$this, 'update'],
            [$this, 'canManage'],
            true
        );
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function listing(): array
    {
        $items = [];
        foreach ($this->types->getPostTypesSettings() as $name => $settings) {
            $items[] = ['name' => $name, 'label' => $settings['label'], 'enabled' => (bool) $settings['active']];
        }
        return ['items' => $items];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function get(array $input)
    {
        $type = $input['post_type'];
        if (!in_array($type, $this->types->getPostTypes(), true)) {
            return Registry::error(__('Choose a supported post type from discovery.', 'post-expirator'));
        }
        $schema = Registry::object([
            'expireType' => ['type' => 'string', 'title' => __('Action', 'post-expirator'), 'enum' => array_keys($this->actions->getActions($type))],
            'newStatus' => ['type' => 'string', 'title' => __('New status', 'post-expirator'), 'enum' => array_keys($this->actions->getStatusesForPostType($type))],
            'autoEnable' => ['type' => 'boolean', 'title' => __('Automatically enable', 'post-expirator')],
            'activeMetaBox' => ['type' => 'boolean', 'title' => __('Enable Future Actions', 'post-expirator')],
            'taxonomy' => ['type' => 'string', 'maxLength' => 100],
            'terms' => ['type' => 'array', 'items' => Registry::id(), 'maxItems' => 100, 'uniqueItems' => true],
            'emailnotification' => ['type' => 'string', 'maxLength' => 10000, 'title' => __('Notification emails, comma separated', 'post-expirator')],
            'default-expire-type' => ['type' => 'string', 'enum' => ['inherit', 'custom']],
            'default-custom-date' => ['type' => 'string', 'maxLength' => 500, 'title' => __('Date offset', 'post-expirator')],
        ]);
        $raw = $this->settings->getPostTypeDefaults($type);
        $values = array_intersect_key($raw, $schema['properties']);
        $values['activeMetaBox'] = !in_array((string) $values['activeMetaBox'], ['inactive', '0', ''], true);
        $values['autoEnable'] = (bool) $values['autoEnable'];
        $values['terms'] = array_values(array_filter(array_map('intval', is_array($values['terms']) ? $values['terms'] : explode(',', (string) $values['terms']))));
        foreach ($values as $key => $value) {
            if ($schema['properties'][$key]['type'] === 'string') {
                $values[$key] = (string) $value;
            }
        }
        $taxonomies = [];
        foreach ($this->wordpress->taxonomies($type) as $taxonomy) {
            if ($this->wordpress->can($taxonomy->cap->assign_terms)) {
                $taxonomies[] = ['name' => $taxonomy->name, 'label' => $taxonomy->label];
            }
        }
        return ['post_type' => $type, 'settings' => (object) $values, 'schema' => $schema,
            'choices' => (object) ['actions' => $this->actions->getActionsAsOptions($type),
                'statuses' => Registry::options($this->actions->getStatusesForPostType($type)), 'taxonomies' => $taxonomies],
            'hash' => $this->wordpress->hash([$type, $raw]), 'dry_run' => false];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function update(array $input)
    {
        $before = $this->get($input);
        if ($before instanceof \WP_Error) {
            return $before;
        }
        if ($before['hash'] !== $input['expected_hash']) {
            return Registry::error(__('These defaults changed. Read them again before saving.', 'post-expirator'), 409);
        }
        $valid = $this->wordpress->validate($input['settings'], $before['schema']);
        if ($valid instanceof \WP_Error || !Registry::strict($input['settings'], $before['schema'])) {
            return $valid instanceof \WP_Error ? $valid : Registry::error(__('Use exact discovered setting types.', 'post-expirator'));
        }
        $next = array_merge((array) $before['settings'], $input['settings']);
        $taxonomy = $next['taxonomy'] !== '' ? $this->wordpress->taxonomy($next['taxonomy']) : null;
        if (
            ($next['terms'] || $next['taxonomy'] !== '') && (!$taxonomy || !in_array($input['post_type'], $taxonomy->object_type, true)
            || !$this->wordpress->can($taxonomy->cap->assign_terms))
        ) {
            return Registry::error(__('Choose an assignable taxonomy for this post type.', 'post-expirator'), 403);
        }
        foreach ($next['terms'] as $id) {
            $term = $this->wordpress->term($id, $next['taxonomy']);
            if (!$term || $term instanceof \WP_Error) {
                return Registry::error(__('Choose existing terms in the selected taxonomy.', 'post-expirator'));
            }
        }
        if ($next['default-expire-type'] === 'custom' && ($next['default-custom-date'] === '' || strtotime($next['default-custom-date']) === false)) {
            return Registry::error(__('Use a valid native date offset.', 'post-expirator'));
        }
        foreach (array_filter(array_map('trim', explode(',', $next['emailnotification']))) as $email) {
            if ($this->wordpress->validate($email, ['type' => 'string', 'format' => 'email']) instanceof \WP_Error) {
                return Registry::error(__('Use valid comma-separated email addresses.', 'post-expirator'));
            }
        }
        if (!empty($input['dry_run'])) {
            return array_merge($before, ['settings' => (object) $next, 'dry_run' => true]);
        }
        $key = 'expirationdateDefaults' . ucfirst($input['post_type']);
        $snapshot = $this->wordpress->snapshotOptions([$key]);
        $raw = array_merge($this->settings->getPostTypeDefaults($input['post_type']), $next);
        $raw['autoEnable'] = $next['autoEnable'] ? '1' : '0';
        $raw['activeMetaBox'] = $next['activeMetaBox'] ? '1' : '0';
        try {
            $this->settings->setPostTypeDefaults($input['post_type'], $raw);
            $this->settings->purgeCache();
            $saved = $this->get($input);
            if ($saved instanceof \WP_Error || (array) $saved['settings'] != $next) {
                throw new \RuntimeException('Verification failed');
            }
            return $saved;
        } catch (\Throwable $error) {
            $restored = $this->wordpress->restoreOptions($snapshot);
            $this->settings->purgeCache();
            return Registry::error($restored ? __('Defaults save failed; original values were restored.', 'post-expirator')
                : __('Defaults save failed and recovery was incomplete. Read the settings before retrying.', 'post-expirator'), 500);
        }
    }
}
