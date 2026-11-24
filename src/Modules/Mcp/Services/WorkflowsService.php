<?php

/**
 * Native workflow discovery and revision-protected editor operations.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

declare(strict_types=1);

namespace PublishPress\Future\Modules\Mcp\Services;

use Closure;
use PublishPress\Future\Framework\WordPress\Facade\McpFacade;
use PublishPress\Future\Modules\Mcp\Registry;
use PublishPress\Future\Modules\Workflows\CapabilitiesAbstract;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;
use PublishPress\Future\Modules\Workflows\Module;

\defined('ABSPATH') || exit;

class WorkflowsService
{
    private McpFacade $wordpress;
    private WorkflowGraph $graph;
    private Closure $factory;
    private WorkflowChoices $choices;

    /** @since 4.12.0 */
    public function __construct(McpFacade $wordpress, WorkflowGraph $graph, Closure $factory, WorkflowChoices $choices)
    {
        $this->wordpress = $wordpress;
        $this->graph = $graph;
        $this->factory = $factory;
        $this->choices = $choices;
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     */
    public function canEdit(array $input = []): bool
    {
        if (!$this->wordpress->can(CapabilitiesAbstract::EDIT_WORKFLOWS)) {
            return false;
        }
        if (empty($input['id'])) {
            return true;
        }
        $post = $this->wordpress->post($input['id']);
        return $post && $post->post_type === Module::POST_TYPE_WORKFLOW
            && $this->wordpress->can('edit_post', $post->ID);
    }

    /** @since 4.12.0 */
    public function register(Registry $registry): void
    {
        $id = Registry::object(['id' => Registry::id()], ['id']);
        $output = ['id' => ['type' => 'integer'], 'title' => ['type' => 'string'], 'description' => ['type' => 'string'],
            'status' => ['type' => 'string'], 'debug' => ['type' => 'object'], 'flow' => ['type' => 'object'], 'hash' => ['type' => 'string'], 'dry_run' => ['type' => 'boolean']];
        $registry->add(
            'get-workflow-step-types',
            __('Discover workflow steps', 'post-expirator'),
            __('Read native Action Workflows step names, labels, settings fields/defaults, validation rules, variable outputs, connection handles and Pro requirements. Nodes use id, type, position and data {name, slug, settings}; edges use id, source, target, sourceHandle and targetHandle. Unavailable Pro steps cannot be saved.', 'post-expirator'),
            Registry::object(),
            ['steps' => ['type' => 'array', 'items' => ['type' => 'object']], 'pro_active' => ['type' => 'boolean'], 'choices' => ['type' => 'object']],
            [$this, 'types'],
            [$this, 'canEdit']
        );
        $registry->add(
            'list-workflows',
            __('Find action workflows', 'post-expirator'),
            __('Find a bounded page of editable Future workflows by title. Includes enabled (publish), disabled (draft) and trashed workflows. Read a workflow before changing it.', 'post-expirator'),
            Registry::object(array_merge(Registry::page(), ['search' => ['type' => 'string', 'maxLength' => 200]])),
            ['items' => ['type' => 'array', 'items' => ['type' => 'object']], 'page' => ['type' => 'integer'], 'total_pages' => ['type' => 'integer']],
            [$this, 'listing'],
            [$this, 'canEdit']
        );
        $registry->add(
            'get-workflow',
            __('Read action workflow', 'post-expirator'),
            __('Read the native editor graph, status, title, description and latest hash. Reading does not upgrade, enable or execute a workflow. Preserve unmentioned graph/editor properties.', 'post-expirator'),
            $id,
            $output,
            [$this, 'get'],
            [$this, 'canEdit']
        );
        $registry->add(
            'save-workflow',
            __('Create or update action workflow', 'post-expirator'),
            __('Create a disabled workflow with title and native flow, or patch an existing ID using expected_hash. Use discovered step settings and handles. Publishing requires publish_publishpress_workflows; disabling requires unpublish_publishpress_workflows. Editing an enabled workflow also requires publishing permission. dry_run validates without saving or executing. Does not accept executable PHP or unrestricted action hooks.', 'post-expirator'),
            Registry::object(['id' => Registry::id(), 'title' => ['type' => 'string', 'minLength' => 1, 'maxLength' => 200],
                'description' => ['type' => 'string', 'maxLength' => 10000], 'status' => ['type' => 'string', 'enum' => ['draft', 'publish']],
                'flow' => ['type' => 'object'], 'debug' => Registry::object(array_fill_keys(['queries', 'emails', 'wordpress_errors', 'running_step'], ['type' => 'boolean'])), 'expected_hash' => Registry::revision(), 'dry_run' => Registry::preview()]),
            $output,
            [$this, 'save'],
            [$this, 'canEdit'],
            true,
            false
        );
        $registry->add(
            'update-workflow-step',
            __('Update one workflow step', 'post-expirator'),
            __('Patch supplied native settings of one verified node_id in a workflow using its latest hash. Preserves other steps, connections and editor presentation. Uses the same edition, graph and publishing checks as saving a workflow. dry_run validates without saving.', 'post-expirator'),
            Registry::object(
                ['id' => Registry::id(), 'node_id' => ['type' => 'string', 'maxLength' => 100],
                'settings' => ['type' => 'object', 'maxProperties' => 100], 'expected_hash' => Registry::revision(), 'dry_run' => Registry::preview()],
                ['id', 'node_id', 'settings', 'expected_hash']
            ),
            $output,
            [$this, 'step'],
            [$this, 'canEdit'],
            true
        );
        $registry->add(
            'duplicate-workflow',
            __('Duplicate action workflow', 'post-expirator'),
            __('Copy a verified workflow into a new disabled draft using its latest hash. Preserves its native graph and debug options without executing it or changing the original. dry_run previews without creating a post.', 'post-expirator'),
            Registry::object(['id' => Registry::id(), 'expected_hash' => Registry::revision(), 'dry_run' => Registry::preview()], ['id', 'expected_hash']),
            $output,
            [$this, 'duplicate'],
            [$this, 'canEdit'],
            true,
            false
        );
        $registry->add(
            'delete-workflow',
            __('Trash action workflow', 'post-expirator'),
            __('Move a verified workflow to Trash using its latest hash and native delete/unpublish permissions. Refuses permanent deletion when Trash is disabled. Does not run pending steps. dry_run checks only.', 'post-expirator'),
            Registry::object(['id' => Registry::id(), 'expected_hash' => Registry::revision(), 'dry_run' => Registry::preview()], ['id', 'expected_hash']),
            ['id' => ['type' => 'integer'], 'dry_run' => ['type' => 'boolean']],
            [$this, 'delete'],
            [$this, 'canEdit'],
            true
        );
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public function types(): array
    {
        return ['steps' => array_values($this->graph->definitions()), 'pro_active' => $this->wordpress->pro(), 'choices' => (object) $this->choices->get()];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return array<array-key, mixed>
     */
    public function listing(array $input): array
    {
        $query = $this->wordpress->posts(['post_type' => Module::POST_TYPE_WORKFLOW, 'post_status' => ['draft', 'publish', 'trash'],
            's' => $input['search'] ?? '', 'posts_per_page' => $input['per_page'] ?? 20, 'paged' => $input['page'] ?? 1,
            'orderby' => 'ID', 'order' => 'ASC']);
        $items = [];
        foreach ($query->posts as $post) {
            if (!$post instanceof \WP_Post) {
                $post = $this->wordpress->post((int) $post);
            }
            if (!$post) {
                continue;
            }
            if ($this->canEdit(['id' => $post->ID])) {
                $items[] = ['id' => $post->ID, 'title' => $post->post_title, 'status' => $post->post_status];
            }
        }
        return ['items' => $items, 'page' => $input['page'] ?? 1, 'total_pages' => (int) $query->max_num_pages];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function get(array $input)
    {
        $model = ($this->factory)();
        if (!$model->load($input['id'])) {
            return Registry::error(__('Workflow not found.', 'post-expirator'), 404);
        }
        $data = ['id' => $model->getId(), 'title' => $model->getTitle(), 'description' => $model->getDescription(),
            'status' => $model->getStatus(), 'flow' => (object) $model->getFlow(false),
            'debug' => (object) ['queries' => $model->isDebugRayShowQueriesEnabled(), 'emails' => $model->isDebugRayShowEmailsEnabled(),
                'wordpress_errors' => $model->isDebugRayShowWordPressErrorsEnabled(), 'running_step' => $model->isDebugRayShowCurrentRunningStepEnabled()]];
        $data['hash'] = $this->wordpress->hash([$data, $this->wordpress->allMeta($model->getId())]);
        $data['dry_run'] = false;
        return $data;
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function step(array $input)
    {
        $state = $this->get($input);
        if ($state instanceof \WP_Error) {
            return $state;
        }
        $flow = (array) $state['flow'];
        foreach ($flow['nodes'] ?? [] as $index => $node) {
            if ($node['id'] === $input['node_id']) {
                $flow['nodes'][$index]['data']['settings'] = array_merge($node['data']['settings'] ?? [], $input['settings']);
                return $this->save(['id' => $input['id'], 'expected_hash' => $input['expected_hash'],
                    'flow' => $flow, 'dry_run' => $input['dry_run'] ?? false]);
            }
        }
        return Registry::error(__('Workflow step not found.', 'post-expirator'), 404);
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function save(array $input)
    {
        $existing = !empty($input['id']);
        $type = $this->wordpress->postType(Module::POST_TYPE_WORKFLOW);
        if (!$existing && (!$type || !$this->wordpress->can($type->cap->create_posts))) {
            return Registry::error(__('You cannot create workflows.', 'post-expirator'), 403);
        }
        $before = $existing ? $this->get($input) : ['id' => 0, 'title' => '', 'description' => '',
            'status' => 'draft', 'debug' => (object) ['queries' => false, 'emails' => false, 'wordpress_errors' => false, 'running_step' => false], 'flow' => (object) ['nodes' => [], 'edges' => []], 'hash' => '', 'dry_run' => false];
        if ($before instanceof \WP_Error) {
            return $before;
        }
        if ($existing && ($input['expected_hash'] ?? '') !== $before['hash']) {
            return Registry::error(__('This workflow changed. Read it again before saving.', 'post-expirator'), 409);
        }
        if ($existing && !in_array($before['status'], ['draft', 'publish'], true)) {
            return Registry::error(__('Restore the workflow in WordPress before editing it.', 'post-expirator'));
        }
        $next = array_merge($before, array_intersect_key($input, array_flip(['title', 'description', 'status', 'flow'])));
        $next['debug'] = (object) array_merge((array) $before['debug'], (array) ($input['debug'] ?? []));
        if (trim($next['title']) === '') {
            return Registry::error(__('A workflow title is required.', 'post-expirator'));
        }
        if (
            ($next['status'] === 'publish' || $before['status'] === 'publish')
            && !$this->wordpress->can(CapabilitiesAbstract::PUBLISH_WORKFLOWS)
        ) {
            return Registry::error(__('You cannot publish or change an enabled workflow.', 'post-expirator'), 403);
        }
        if (
            $before['status'] === 'publish' && $next['status'] !== 'publish'
            && !$this->wordpress->can(CapabilitiesAbstract::UNPUBLISH_WORKFLOWS)
        ) {
            return Registry::error(__('You cannot disable this workflow.', 'post-expirator'), 403);
        }
        $flow = $this->graph->prepare((array) $next['flow'], $next['status'] === 'publish');
        if ($flow instanceof \WP_Error) {
            return $flow;
        }
        $flow['editorVersion'] = PUBLISHPRESS_FUTURE_VERSION;
        $next['flow'] = (object) $flow;
        $next['title'] = $this->wordpress->text($next['title']);
        $next['description'] = $this->wordpress->html($next['description']);
        if (!empty($input['dry_run'])) {
            return array_merge($next, ['dry_run' => true]);
        }
        $snapshot = null;
        if ($existing) {
            $snapshot = $this->wordpress->post($input['id']);
            if (!$snapshot) {
                return Registry::error(__('This workflow disappeared. Read the workflow list again.', 'post-expirator'), 409);
            }
            $snapshot = clone $snapshot;
        }
        $metadata = $existing ? $this->wordpress->allMeta($input['id']) : [];
        $model = ($this->factory)();
        $id = $existing ? $input['id'] : 0;
        try {
            if ($existing) {
                $model->load($id);
            } else {
                $id = $model->createNew(false);
                if (!$id) {
                    throw new \RuntimeException('Creation failed');
                }
            }
            $model->setTitle($next['title']);
            $model->setDescription($next['description']);
            $model->setFlow($flow);
            $model->setStatus($next['status']);
            $model->setDebugRayShowQueries($next['debug']->queries);
            $model->setDebugRayShowEmails($next['debug']->emails);
            $model->setDebugRayShowWordPressErrors($next['debug']->wordpress_errors);
            $model->setDebugRayShowCurrentRunningStep($next['debug']->running_step);
            if ($next['status'] === 'publish' && $before['status'] !== 'publish') {
                $model->publish();
            } elseif ($before['status'] === 'publish' && $next['status'] === 'draft') {
                $model->unpublish();
            } else {
                $model->save();
            }
            $saved = $this->get(['id' => $id]);
            foreach (['title', 'description', 'status', 'flow', 'debug'] as $key) {
                if ($saved instanceof \WP_Error || $this->wordpress->hash($saved[$key]) !== $this->wordpress->hash($next[$key])) {
                    throw new \RuntimeException('Verification failed');
                }
            }
            return $saved;
        } catch (\Throwable $error) {
            $restored = $snapshot instanceof \WP_Post ? $this->restoreWorkflow($snapshot, $metadata) : (!$id || $this->wordpress->deleteDraft($id));
            if ($existing && $restored) {
                $state = $this->get(['id' => $id]);
                $restored = !($state instanceof \WP_Error) && $state['hash'] === $before['hash'];
            }
            return Registry::error($restored ? __('Workflow save failed; workflow data was restored. Inspect scheduled actions before retrying because native save hooks may have run.', 'post-expirator')
                : __('Workflow save failed and recovery was incomplete. Inspect the workflow before retrying.', 'post-expirator'), 500);
        }
    }

    /**
     * Remove metadata created by a failed native workflow save when it was absent in the snapshot.
     *
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $metadata
     */
    private function restoreWorkflow(\WP_Post $post, array $metadata): bool
    {
        $keys = [
            WorkflowModel::META_KEY_HAS_LEGACY_TRIGGER,
            WorkflowModel::META_KEY_HAS_MANUAL_TRIGGER,
            WorkflowModel::META_KEY_DEBUG_RAY_SHOW_QUERIES,
            WorkflowModel::META_KEY_DEBUG_RAY_SHOW_EMAILS,
            WorkflowModel::META_KEY_DEBUG_RAY_SHOW_WORDPRESS_ERRORS,
            WorkflowModel::META_KEY_DEBUG_RAY_SHOW_CURRENT_RUNNING_STEP,
        ];
        foreach ($keys as $key) {
            $metadata[$key] = $metadata[$key] ?? [];
        }
        return $this->wordpress->restorePost($post, $metadata);
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function delete(array $input)
    {
        $before = $this->get($input);
        if ($before instanceof \WP_Error) {
            return $before;
        }
        if ($before['hash'] !== $input['expected_hash']) {
            return Registry::error(__('This workflow changed. Read it again before deleting.', 'post-expirator'), 409);
        }
        if (!$this->wordpress->can('delete_post', $input['id']) || !$this->wordpress->can(CapabilitiesAbstract::UNPUBLISH_WORKFLOWS)) {
            return Registry::error(__('You cannot delete this workflow.', 'post-expirator'), 403);
        }
        if (empty($input['dry_run']) && !$this->wordpress->trash($input['id'])) {
            return Registry::error(__('The workflow could not be moved to Trash.', 'post-expirator'), 500);
        }
        return ['id' => $input['id'], 'dry_run' => !empty($input['dry_run'])];
    }
    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function duplicate(array $input)
    {
        $before = $this->get($input);
        if ($before instanceof \WP_Error) {
            return $before;
        }
        if ($before['hash'] !== $input['expected_hash']) {
            return Registry::error(__('This workflow changed. Read it again before copying.', 'post-expirator'), 409);
        }
        return $this->save(['title' => sprintf(
            /* translators: %s is the original workflow title. */
            __('%s #2', 'post-expirator'),
            $before['title']
        ),
            'description' => $before['description'], 'flow' => (array) $before['flow'], 'debug' => (array) $before['debug'],
            'dry_run' => $input['dry_run'] ?? false]);
    }
}
