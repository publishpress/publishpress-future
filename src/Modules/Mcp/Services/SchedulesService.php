<?php

/**
 * Read and schedule simple Future actions through the native scheduler.
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
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Expirator\Interfaces\SchedulerInterface;
use PublishPress\Future\Modules\Expirator\Models\CurrentUserModel;
use PublishPress\Future\Modules\Expirator\Models\ExpirationActionsModel;
use PublishPress\Future\Modules\Mcp\Registry;
use PublishPress\Future\Modules\Settings\Models\SettingsPostTypesModel;
use PublishPress\Future\Modules\Workflows\CapabilitiesAbstract;
use PublishPress\Future\Modules\Workflows\Domain\LegacyAction\TriggerWorkflow;
use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnLegacyActionTrigger;

\defined('ABSPATH') || exit;

class SchedulesService
{
    private McpFacade $wordpress;
    private SchedulerInterface $scheduler;
    private Closure $postFactory;
    private CurrentUserModel $user;
    private ExpirationActionsModel $actions;
    private SettingsPostTypesModel $postTypes;
    private Closure $workflowFactory;
    private Closure $argsFactory;

    /** @since 4.12.0 */
    public function __construct(McpFacade $wordpress, SchedulerInterface $scheduler, Closure $postFactory, CurrentUserModel $user, ExpirationActionsModel $actions, SettingsPostTypesModel $postTypes, Closure $workflowFactory, Closure $argsFactory)
    {
        $this->wordpress = $wordpress;
        $this->scheduler = $scheduler;
        $this->postFactory = $postFactory;
        $this->user = $user;
        $this->actions = $actions;
        $this->postTypes = $postTypes;
        $this->workflowFactory = $workflowFactory;
        $this->argsFactory = $argsFactory;
    }

    /**
     * @since 4.12.0
     *
     * @return array<array-key, mixed>
     */
    public static function input(): array
    {
        return Registry::object([
            'post_id' => Registry::id(), 'expected_hash' => Registry::revision(),
            'enabled' => ['type' => 'boolean'], 'date' => ['type' => 'string', 'format' => 'date-time'],
            'action' => ['type' => 'string', 'maxLength' => 100], 'new_status' => ['type' => 'string', 'maxLength' => 100],
            'taxonomy' => ['type' => 'string', 'maxLength' => 100],
            'term_ids' => ['type' => 'array', 'maxItems' => 100, 'uniqueItems' => true, 'items' => Registry::id()],
            'workflow_id' => Registry::id(), 'dry_run' => Registry::preview(),
        ], ['post_id', 'expected_hash']);
    }

    /** @since 4.12.0 */
    public function register(Registry $registry): void
    {
        $output = ['post_id' => ['type' => 'integer'], 'action_id' => ['type' => 'integer'], 'settings' => ['type' => 'object'],
            'hash' => ['type' => 'string'], 'timezone' => ['type' => 'string'], 'dry_run' => ['type' => 'boolean']];
        $registry->add(
            'get-post-action',
            __('Read a post\'s Future action', 'post-expirator'),
            __('Read the saved simple Future action, UTC schedule, native action/status, taxonomy terms and revision. Requires Future scheduling and edit_post permissions. Does not execute or synchronize an action.', 'post-expirator'),
            Registry::object(['post_id' => Registry::id()], ['post_id']),
            $output,
            [$this, 'get'],
            [$this, 'canEdit']
        );
        $registry->add(
            'get-post-action-choices',
            __('Discover Future actions for a post', 'post-expirator'),
            __('Read native action/status choices, enabled post-type defaults, taxonomy names and bounded term choices. Permission-filtered statuses include active Pro custom statuses. Authorize each post separately.', 'post-expirator'),
            Registry::object(array_merge(['post_id' => Registry::id(), 'taxonomy' => ['type' => 'string', 'maxLength' => 100]], Registry::page()), ['post_id']),
            ['actions' => ['type' => 'array', 'items' => ['type' => 'object']], 'statuses' => ['type' => 'array', 'items' => ['type' => 'object']],
                'defaults' => ['type' => 'object'], 'taxonomies' => ['type' => 'array', 'items' => ['type' => 'object']], 'terms' => ['type' => 'array', 'items' => ['type' => 'object']], 'timezone' => ['type' => 'string']],
            [$this, 'choices'],
            [$this, 'canEdit']
        );
        $registry->add(
            'update-post-action',
            __('Schedule, change or cancel a Future action', 'post-expirator'),
            __('Patch an existing post\'s simple Future action using its latest hash. enabled=false cancels it. Dates require an ISO 8601 timezone offset and must be in the future. Native status publishing, deletion, sticky-post, taxonomy and Pro workflow permissions apply. Omitted settings are preserved. dry_run validates without scheduling.', 'post-expirator'),
            self::input(),
            $output,
            [$this, 'update'],
            [$this, 'canEdit'],
            true
        );
        $registry->add(
            'bulk-update-post-actions',
            __('Update Future actions for several posts', 'post-expirator'),
            __('Validate all explicit posts, hashes and action permissions before any schedule changes. Preserves unrelated posts. dry_run previews all changes; failed saves attempt to restore applied schedules and report incomplete recovery.', 'post-expirator'),
            Registry::object(['changes' => ['type' => 'array', 'minItems' => 1, 'maxItems' => 50, 'items' => self::input()], 'dry_run' => Registry::preview()], ['changes']),
            ['results' => ['type' => 'array', 'items' => ['type' => 'object']], 'dry_run' => ['type' => 'boolean']],
            [$this, 'bulk'],
            [$this, 'canBulk'],
            true
        );
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     */
    public function canEdit(array $input = []): bool
    {
        $post = $this->wordpress->post((int) ($input['post_id'] ?? 0));
        if (!$post || !$this->user->userCanExpirePosts() || !$this->user->userCanEditPost($post->ID)) {
            return false;
        }
        return !empty($this->postTypes->getPostTypesSettings()[$post->post_type]['active']);
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     */
    public function canBulk(array $input = []): bool
    {
        foreach ($input['changes'] ?? [] as $change) {
            if (!$this->canEdit($change)) {
                return false;
            }
        }
        return !empty($input['changes']);
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return array<array-key, mixed>
     */
    public function get(array $input): array
    {
        $post = ($this->postFactory)($input['post_id']);
        $raw = $post->getExpirationDataAsArray();
        $settings = ['enabled' => (bool) $raw['enabled'], 'timestamp' => $raw['enabled'] ? (int) $raw['date'] : 0,
            'action' => (string) $raw['expireType'], 'new_status' => (string) $raw['newStatus'],
            'taxonomy' => (string) $raw['categoryTaxonomy'], 'term_ids' => array_map('intval', $raw['category']),
            'workflow_id' => (int) ($raw['extraData']['workflowId'] ?? 0)];
        return $this->response($input['post_id'], $settings);
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $settings
     * @return array<array-key, mixed>
     */
    private function response(int $postId, array $settings, bool $preview = false, ?string $hash = null): array
    {
        $args = ($this->argsFactory)();
        $args->loadByPostId($postId, true);
        $actionId = $args->getCronActionId();
        return ['post_id' => $postId, 'action_id' => $actionId, 'settings' => (object) $settings,
            'hash' => $hash ?? $this->wordpress->hash([$postId, $settings, $actionId]),
            'timezone' => $this->wordpress->timezone(), 'dry_run' => $preview];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function choices(array $input)
    {
        $post = $this->wordpress->post($input['post_id']);
        if (!$post) {
            return Registry::error(__('This post no longer exists.', 'post-expirator'), 404);
        }
        $choices = $this->user->filterScheduleSelectOptions(
            $this->actions->getActionsAsOptions($post->post_type),
            Registry::options($this->actions->getStatusesForPostType($post->post_type)),
            $post->post_type,
            $post->ID
        );
        $terms = [];
        if (!empty($input['taxonomy'])) {
            $taxonomy = $this->wordpress->taxonomy($input['taxonomy']);
            if (!$taxonomy || !in_array($post->post_type, $taxonomy->object_type, true) || !$this->wordpress->can($taxonomy->cap->assign_terms)) {
                return Registry::error(__('Choose an assignable taxonomy for this post type.', 'post-expirator'), 403);
            }
            $found = $this->wordpress->terms(['taxonomy' => $taxonomy->name, 'hide_empty' => false,
                'number' => $input['per_page'] ?? 20, 'offset' => (($input['page'] ?? 1) - 1) * ($input['per_page'] ?? 20)]);
            if ($found instanceof \WP_Error) {
                return $found;
            }
            foreach ($found as $term) {
                $terms[] = ['id' => $term->term_id, 'name' => $term->name, 'slug' => $term->slug];
            }
        }
        $taxonomies = [];
        foreach ($this->wordpress->taxonomies($post->post_type) as $taxonomy) {
            if ($this->wordpress->can($taxonomy->cap->assign_terms)) {
                $taxonomies[] = ['name' => $taxonomy->name, 'label' => $taxonomy->label];
            }
        }
        return ['taxonomies' => $taxonomies, 'actions' => array_values($choices['actions']), 'statuses' => array_values($choices['statuses']),
            'defaults' => (object) $this->postTypes->getPostTypesSettings()[$post->post_type],
            'terms' => $terms, 'timezone' => $this->wordpress->timezone()];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function plan(array $input)
    {
        $before = $this->get($input);
        if ($input['expected_hash'] !== $before['hash']) {
            return Registry::error(__('This Future action changed. Read it again before saving.', 'post-expirator'), 409);
        }
        $settings = array_merge((array) $before['settings'], array_intersect_key($input, (array) $before['settings']));
        if (isset($input['date'])) {
            if (!preg_match('/(Z|[+-][0-9]{2}:[0-9]{2})$/D', $input['date'])) {
                return Registry::error(__('Include a timezone offset in the schedule date.', 'post-expirator'));
            }
            try {
                $settings['timestamp'] = (new \DateTimeImmutable($input['date']))->getTimestamp();
                $dateErrors = \DateTimeImmutable::getLastErrors();
                if ($dateErrors && ($dateErrors['warning_count'] || $dateErrors['error_count'])) {
                    return Registry::error(__('Use a real calendar date and time.', 'post-expirator'));
                }
            } catch (\Throwable $error) {
                return Registry::error(__('Use a valid ISO 8601 date.', 'post-expirator'));
            }
        }
        if ($settings['enabled']) {
            $post = $this->wordpress->post($input['post_id']);
            if (!$post) {
                return Registry::error(__('This post no longer exists.', 'post-expirator'), 404);
            }
            $actions = $this->actions->getActions($post->post_type);
            if ($settings['timestamp'] <= $this->wordpress->now() || !isset($actions[$settings['action']])) {
                return Registry::error(__('Choose a future date and an available native action.', 'post-expirator'));
            }
            if (!$this->wordpress->canRepresentSchedule($settings['timestamp'])) {
                return Registry::error(__('The native scheduler cannot represent this ambiguous daylight-saving time. Choose a time outside the transition.', 'post-expirator'));
            }
            if (!$this->user->userCanScheduleAction($post->ID, $settings['action'], $settings['new_status'])) {
                return Registry::error(__('You cannot schedule this action or post status.', 'post-expirator'), 403);
            }
            if ($settings['action'] === TriggerWorkflow::ACTION_NAME) {
                $workflow = ($this->workflowFactory)();
                if (
                    !$workflow->load($settings['workflow_id']) || !$workflow->isActive()
                    || !$this->wordpress->can('read_post', $settings['workflow_id'])
                ) {
                    return Registry::error(__('Choose an enabled workflow you can read.', 'post-expirator'), 403);
                }
                $triggers = array_filter($workflow->getFlow(false)['nodes'] ?? [], static function ($node) {
                    return ($node['data']['name'] ?? '') === OnLegacyActionTrigger::getNodeTypeName();
                });
                if (!$triggers) {
                    return Registry::error(__('This workflow needs a Future Actions trigger.', 'post-expirator'));
                }
            }
            $valid = strpos($settings['action'], 'category') === 0 ? $this->validateTerms($post->post_type, $settings) : true;
            if ($valid instanceof \WP_Error) {
                return $valid;
            }
        }
        return ['before' => $before, 'after' => $this->response($input['post_id'], $settings, true, $before['hash'])];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $settings
     * @return mixed
     */
    private function validateTerms(string $postType, array $settings)
    {
        if ($settings['taxonomy'] === '' || (!$settings['term_ids'] && $settings['action'] !== ExpirationActionsAbstract::POST_CATEGORY_REMOVE_ALL)) {
            return Registry::error(__('Choose a taxonomy and the terms required by this action.', 'post-expirator'));
        }
        $taxonomy = $this->wordpress->taxonomy($settings['taxonomy']);
        if (!$taxonomy || !in_array($postType, $taxonomy->object_type, true) || !$this->wordpress->can($taxonomy->cap->assign_terms)) {
            return Registry::error(__('You cannot assign this taxonomy to the post.', 'post-expirator'), 403);
        }
        foreach ($settings['term_ids'] as $id) {
            $term = $this->wordpress->term($id, $taxonomy->name);
            if (!$term || $term instanceof \WP_Error) {
                return Registry::error(__('Choose existing terms from the selected taxonomy.', 'post-expirator'));
            }
        }
        return true;
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function update(array $input)
    {
        return $this->bulk(['changes' => [$input], 'dry_run' => !empty($input['dry_run'])], true);
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $input
     * @return mixed
     */
    public function bulk(array $input, bool $single = false)
    {
        $plans = [];
        foreach ($input['changes'] as $change) {
            if (isset($plans[$change['post_id']])) {
                return Registry::error(__('Include each post only once.', 'post-expirator'));
            }
            $plan = $this->plan($change);
            if ($plan instanceof \WP_Error) {
                return $plan;
            }
            $plans[$change['post_id']] = $plan;
        }
        $results = $applied = [];
        foreach ($plans as $plan) {
            if (!empty($input['dry_run'])) {
                $results[] = $plan['after'];
                continue;
            }
            $applied[] = $plan;
            $result = $this->persist($plan['after']);
            if ($result instanceof \WP_Error) {
                $restored = true;
                foreach (array_reverse($applied) as $restore) {
                    $restored = !($this->persist($restore['before']) instanceof \WP_Error) && $restored;
                }
                return $restored ? $result : Registry::error(__('Schedule save failed and recovery was incomplete. Read every target post before retrying.', 'post-expirator'), 500);
            }
            $results[] = $result;
        }
        return $single ? $results[0] : ['results' => $results, 'dry_run' => !empty($input['dry_run'])];
    }

    /**
     * @since 4.12.0
     *
     * @param array<array-key, mixed> $state
     * @return mixed
     */
    private function persist(array $state)
    {
        $settings = (array) $state['settings'];
        try {
            if (!$settings['enabled']) {
                $this->scheduler->unschedule($state['post_id']);
            } else {
                $this->scheduler->schedule($state['post_id'], $this->wordpress->localTimestamp($settings['timestamp']), [
                'expireType' => $settings['action'], 'newStatus' => $settings['new_status'],
                'category' => $settings['term_ids'], 'categoryTaxonomy' => $settings['taxonomy'],
                'extraData' => $settings['workflow_id'] ? ['workflowId' => $settings['workflow_id']] : [],
                ]);
            }
            $saved = $this->get(['post_id' => $state['post_id']]);
            $actual = (array) $saved['settings'];
            if ($actual['enabled'] !== $settings['enabled'] || ($settings['enabled'] && $actual !== $settings)) {
                return Registry::error(__('The native scheduler could not save the complete Future action.', 'post-expirator'), 500);
            }
            return $saved;
        } catch (\Throwable $error) {
            return Registry::error(__('The native scheduler could not save the Future action.', 'post-expirator'), 500);
        }
    }
    /** Recheck the native action-specific permissions immediately before execution. @since 4.12.0 */
    public function canRun(int $postId): bool
    {
        $post = $this->wordpress->post($postId);
        if (!$post || !$this->canEdit(['post_id' => $postId])) {
            return false;
        }
        $settings = (array) $this->get(['post_id' => $postId])['settings'];
        if (!$settings['enabled'] || !$this->user->userCanScheduleAction($postId, $settings['action'], $settings['new_status'])) {
            return false;
        }
        if (strpos($settings['action'], 'category') === 0) {
            return !($this->validateTerms($post->post_type, $settings) instanceof \WP_Error);
        }
        if ($settings['action'] === TriggerWorkflow::ACTION_NAME) {
            $workflow = ($this->workflowFactory)();
            return $workflow->load($settings['workflow_id']) && $workflow->isActive()
                && $this->wordpress->can(CapabilitiesAbstract::PUBLISH_WORKFLOWS)
                && $this->wordpress->can('read_post', $settings['workflow_id']);
        }
        return true;
    }
}
