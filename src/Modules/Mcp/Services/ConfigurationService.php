<?php

/**
 * Bounded configuration export and validated draft workflow imports.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp\Services;

use PublishPress\Future\Framework\WordPress\Facade\McpFacade;
use PublishPress\Future\Modules\Mcp\Registry;

\defined('ABSPATH') || exit;

class ConfigurationService
{
    private McpFacade $wordpress;
    private SettingsService $settings;
    private PostTypeSettingsService $postTypes;
    private WorkflowsService $workflows;

    /** @since 4.12.0 */
    public function __construct(McpFacade $wordpress, SettingsService $settings, PostTypeSettingsService $postTypes, WorkflowsService $workflows)
    {
        $this->wordpress = $wordpress;
        $this->settings = $settings;
        $this->postTypes = $postTypes;
        $this->workflows = $workflows;
    }

    /** @since 4.12.0 */
    public function canManage(): bool
    {
        return $this->wordpress->can('manage_options');
    }

    /** @since 4.12.0 */
    public function canImport(): bool
    {
        return $this->canManage() && $this->workflows->canEdit();
    }

    /** @since 4.12.0 */
    public function register(Registry $registry): void
    {
        $registry->add(
            'export-configuration',
            __('Export selected Future configuration', 'post-expirator'),
            __('Export explicit workflow IDs, settings groups and post types using Future backup version/settings/workflows fields. Uses the same non-secret projections as discovery and enforces workflow object permissions. Never exports credentials or raw execution contexts. Omitted scopes are not exported; make separate bounded requests for large sites.', 'post-expirator'),
            Registry::object(['workflow_ids' => ['type' => 'array', 'items' => Registry::id(), 'maxItems' => 50, 'uniqueItems' => true],
                'groups' => ['type' => 'array', 'maxItems' => 5, 'uniqueItems' => true, 'items' => ['type' => 'string', 'enum' => ['general', 'notifications', 'display', 'admin', 'advanced']]],
                'post_types' => ['type' => 'array', 'maxItems' => 50, 'uniqueItems' => true, 'items' => ['type' => 'string', 'maxLength' => 100]]]),
            ['configuration' => ['type' => 'object']],
            [$this, 'export'],
            [$this, 'canManage']
        );
        $registry->add(
            'import-workflows',
            __('Import workflows as disabled drafts', 'post-expirator'),
            __('Import up to 20 workflow records from a Future backup workflows array. Validates every graph, native step field and edition before creating any drafts. Source IDs/statuses are not reused; workflows remain disabled. dry_run validates all records without saving. Settings imports use the individual typed update actions with current revision hashes, not arbitrary option data.', 'post-expirator'),
            Registry::object(['workflows' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 20,
                'items' => ['type' => 'object', 'properties' => ['title' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 200],
                    'description' => ['type' => 'string', 'maxLength' => 10000], 'flow' => ['type' => 'object']], 'required' => ['title', 'flow']]],
                'dry_run' => Registry::preview()], ['workflows']),
            ['workflows' => ['type' => 'array', 'items' => ['type' => 'object']], 'dry_run' => ['type' => 'boolean']],
            [$this, 'import'],
            [$this, 'canImport'],
            true,
            false
        );
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function export(array $input)
    {
        $data = ['version' => PUBLISHPRESS_FUTURE_VERSION, 'date' => gmdate('Y-m-d H:i:s'), 'settings' => [], 'workflows' => []];
        foreach ($input['groups'] ?? [] as $group) {
            $data['settings'][$group] = $this->settings->get(['group' => $group])['settings'];
        }
        foreach ($input['post_types'] ?? [] as $type) {
            $state = $this->postTypes->get(['post_type' => $type]);
            if ($state instanceof \WP_Error) {
                return $state;
            }
            $data['settings']['postTypesDefaults'][$type] = $state['settings'];
        }
        foreach ($input['workflow_ids'] ?? [] as $id) {
            if (!$this->workflows->canEdit(['id' => $id])) {
                return Registry::error(__('You cannot export a selected workflow.', 'post-expirator'), 403);
            }
            $state = $this->workflows->get(['id' => $id]);
            if ($state instanceof \WP_Error) {
                return $state;
            }
            $data['workflows'][] = array_intersect_key($state, array_flip(['id', 'title', 'description', 'flow', 'status']));
        }
        $data['settings'] = (object) $data['settings'];
        return ['configuration' => (object) $data];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function import(array $input)
    {
        $plans = [];
        foreach ($input['workflows'] as $record) {
            $record = array_intersect_key($record, array_flip(['title', 'description', 'flow']));
            $plan = $this->workflows->save($record + ['dry_run' => true]);
            if ($plan instanceof \WP_Error) {
                return $plan;
            }
            $plans[] = $record;
        }
        $results = [];
        foreach ($plans as $record) {
            $result = $this->workflows->save($record + ['dry_run' => !empty($input['dry_run'])]);
            if ($result instanceof \WP_Error) {
                $restored = true;
                foreach (array_reverse($results) as $created) {
                    if ($created['id']) {
                        $restored = $this->wordpress->deleteDraft($created['id']) && $restored;
                    }
                }
                return Registry::error($restored ? __('Workflow import failed; created drafts were removed.', 'post-expirator')
                    : __('Workflow import failed and recovery was incomplete. Inspect newly created drafts before retrying.', 'post-expirator'), 500);
            }
            $results[] = $result;
        }
        return ['workflows' => $results, 'dry_run' => !empty($input['dry_run'])];
    }
}
