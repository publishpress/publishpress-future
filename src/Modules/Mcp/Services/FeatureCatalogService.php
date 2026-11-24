<?php

/**
 * Discover Future feature scopes without loading content or granting permissions.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp\Services;

use PublishPress\Future\Framework\WordPress\Facade\McpFacade;
use PublishPress\Future\Modules\Expirator\CapabilitiesAbstract as ExpiratorCapabilities;
use PublishPress\Future\Modules\Mcp\Registry;
use PublishPress\Future\Modules\Workflows\CapabilitiesAbstract as WorkflowCapabilities;

\defined('ABSPATH') || exit;

class FeatureCatalogService
{
    private McpFacade $wordpress;

    /** @since 4.12.0 */
    public function __construct(McpFacade $wordpress)
    {
        $this->wordpress = $wordpress;
    }

    /** @since 4.12.0 */
    public function canDiscover(): bool
    {
        return $this->wordpress->can('manage_options') || $this->wordpress->can(WorkflowCapabilities::EDIT_WORKFLOWS)
            || $this->wordpress->can(ExpiratorCapabilities::EXPIRE_POST);
    }

    /** @since 4.12.0 */
    public function register(Registry $registry): void
    {
        $registry->add(
            'list-features',
            __('Discover Future features and actions', 'post-expirator'),
            __('Discover Future Actions, workflows, per-post selection, settings, scheduled actions and Pro features. Reports action names, edition and permission requirements. Use each feature read action for exact field types, current values, choices and revision hashes. Discovery never grants access.', 'post-expirator'),
            Registry::object(),
            ['features' => ['type' => 'array', 'items' => ['type' => 'object']], 'pro_active' => ['type' => 'boolean']],
            [$this, 'get'],
            [$this, 'canDiscover']
        );
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function get(): array
    {
        $rows = [
            ['post-actions', __('Future Actions in the editor, Quick Edit and Bulk Edit', 'post-expirator'), false,
                [ExpiratorCapabilities::EXPIRE_POST, 'edit_post (each target)', 'native action-specific permissions'],
                ['get-post-action', 'get-post-action-choices'], ['update-post-action', 'bulk-update-post-actions']],
            ['workflows', __('Action Workflows and workflow step settings', 'post-expirator'), false,
                [WorkflowCapabilities::EDIT_WORKFLOWS, 'edit_post (workflow)', 'publish/unpublish capabilities when changing enabled workflows'],
                ['get-workflow-step-types', 'list-workflows', 'get-workflow'], ['save-workflow', 'update-workflow-step', 'duplicate-workflow', 'delete-workflow']],
            ['post-workflows', __('Manual workflow selection and execution for a post', 'post-expirator'), false,
                [ExpiratorCapabilities::EXPIRE_POST, 'edit_post (target)', 'read_post (eligible workflows)'],
                ['get-post-workflows'], ['update-post-workflows', 'run-post-workflow']],
            ['settings', __('General, Notifications, Display, Admin and Advanced settings', 'post-expirator'), false,
                ['manage_options'], ['get-plugin-settings'], ['update-plugin-settings']],
            ['post-types', __('Post Types: Future Action defaults', 'post-expirator'), false,
                ['manage_options', 'assign_terms (selected taxonomy)'], ['list-post-types', 'get-post-type-settings'], ['update-post-type-settings']],
            ['scheduled-actions', __('Scheduled Actions and workflow steps', 'post-expirator'), false,
                ['manage_options', 'related post/workflow permissions', 'native action-specific permissions for run now'],
                ['list-scheduled-actions', 'get-scheduled-action'], ['cancel-scheduled-action', 'run-scheduled-action']],
            ['backup', __('Configuration export and disabled workflow imports', 'post-expirator'), false,
                ['manage_options', 'workflow object permissions'], ['export-configuration'], ['import-workflows']],
            ['debug', __('Debug logging status and counts', 'post-expirator'), false,
                ['manage_options'], ['get-debug-status'], ['update-plugin-settings']],
            ['pro-settings', __('Pro custom statuses, base date, metadata mappings and hidden metabox fields', 'post-expirator'), true,
                ['manage_options'], ['get-pro-settings'], ['update-pro-settings']],
            ['post-row-actions', __('Pro workflow buttons in the posts list', 'post-expirator'), true,
                [ExpiratorCapabilities::EXPIRE_POST, 'edit_post (target)', 'read_post (workflow)', 'native trigger query and confirmation'],
                ['get-post-row-actions'], ['run-post-row-action']],
            ['notifications', __('Pro notifications and offered responses', 'post-expirator'), true,
                ['authenticated recipient ownership'], ['list-notifications', 'get-notification'], ['respond-to-notification']],
        ];
        $features = [];
        foreach ($rows as [$name, $label, $pro, $capabilities, $reads, $writes]) {
            $features[] = ['feature' => $name, 'label' => $label, 'requires_pro' => $pro,
                'available' => !$pro || $this->wordpress->pro(), 'permissions' => $capabilities,
                'read_actions' => $reads, 'write_actions' => $writes];
        }
        return ['features' => $features, 'pro_active' => $this->wordpress->pro()];
    }
}
