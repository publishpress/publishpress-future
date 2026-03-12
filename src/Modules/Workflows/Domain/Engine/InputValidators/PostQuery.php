<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\InputValidators;

use PublishPress\Future\Framework\Logger\LoggerInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\InputValidatorsInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\JsonLogicEngineInterface;
use PublishPress\Future\Modules\Workflows\Module;

class PostQuery implements InputValidatorsInterface
{
    private const LOG_PREFIX = '[PostQueryValidator:%d]: ';

    private ExecutionContextInterface $executionContext;

    private JsonLogicEngineInterface $jsonLogicEngine;

    private LoggerInterface $logger;

    public function __construct(
        ExecutionContextInterface $executionContext,
        JsonLogicEngineInterface $jsonLogicEngine,
        LoggerInterface $logger
    ) {
        $this->executionContext = $executionContext;
        $this->jsonLogicEngine = $jsonLogicEngine;
        $this->logger = $logger;
    }

    public function validate(array $args): bool
    {
        $post = $args['post'];
        $node = $args['node'];
        $nodeSettings = $node['data']['settings'] ?? [];

        if ($this->isLegacyPostQuery($nodeSettings)) {
            return $this->validateLegacyPostQuery($post, $nodeSettings);
        }

        return $this->validateJsonPostQuery($args['post'] ?? null, $nodeSettings);
    }

    private function validateLegacyPostQuery($post, array $nodeSettings): bool
    {
        if (! $this->hasValidPost($post)) {
            return false;
        }

        if (! $this->hasValidPostType($post, $nodeSettings)) {
            return false;
        }

        if (! $this->hasValidPostId($post, $nodeSettings)) {
            return false;
        }

        if (! $this->hasValidPostStatus($post, $nodeSettings)) {
            return false;
        }

        if (! $this->hasValidPostAuthor($post, $nodeSettings)) {
            return false;
        }

        if (! $this->hasValidPostTerms($post, $nodeSettings)) {
            $this->logValidationResult(
                false,
                'Post has none of the required terms',
                $post,
                ['required' => $nodeSettings['postQuery']['postTerms']]
            );

            return false;
        }

        $this->logValidationResult(true, 'Post query conditions evaluated to true', $post, []);

        return true;
    }

    /**
     * @param \WP_Post|object|null $post
     */
    private function validateJsonPostQuery($post, array $nodeSettings): bool
    {
        $json = $nodeSettings['postQuery']['json'] ?? [];

        if (empty($json)) {
            $this->logValidationResult(
                false,
                'JSON Logic post query is empty (no rules configured)',
                $post
            );

            return false;
        }

        $json = $this->executionContext->resolveExpressionsInJsonLogic($json);

        $result = $this->jsonLogicEngine->apply($json, []);

        if (! is_bool($result)) {
            $this->logValidationResult(
                false,
                'JSON Logic result is not boolean',
                $post,
                ['result_type' => gettype($result), 'result' => $result]
            );

            return false;
        }

        if (! $result) {
            $this->logValidationResult(
                false,
                'JSON Logic post query conditions evaluated to false',
                $post,
                ['json_logic_query' => $json]
            );

            return false;
        }

        $this->logValidationResult(true, 'JSON Logic post query conditions evaluated to true', $post, ['json_logic_query' => $json]);

        return true;
    }

    private function isLegacyPostQuery($nodeSettings)
    {
        return ! isset($nodeSettings['postQuery']['json']) && isset($nodeSettings['postQuery']['postType']);
    }

    private function hasValidPost($post)
    {
        if (! is_object($post)) {
            return false;
        }

        if (is_wp_error($post)) {
            throw new \Exception(esc_html('Invalid post object: ' . $post->get_error_message()));
        }

        return true;
    }

    private function hasValidPostType($post, array $nodeSettings): bool
    {
        // Prevent to apply actions to workflows
        if ($post->post_type === Module::POST_TYPE_WORKFLOW) {
            $this->logValidationResult(
                false,
                'Post type is workflow (restricted)',
                $post,
                ['configured' => [Module::POST_TYPE_WORKFLOW]]
            );

            return false;
        }

        $settingPostTypes = $nodeSettings['postQuery']['postType'] ?? [];

        // Invalidate nodes that don't specify a post type to avoid applying actions to all post types
        if (empty($settingPostTypes)) {
            $this->logValidationResult(
                false,
                'No post types configured in step (post type filter is required)',
                $post
            );

            return false;
        }

        if (! empty($settingPostTypes) && ! in_array($post->post_type, $settingPostTypes)) {
            $this->logValidationResult(
                false,
                'Post type does not match',
                $post,
                ['post_type' => $post->post_type, 'allowed' => $settingPostTypes]
            );

            return false;
        }

        return true;
    }

    private function hasValidPostId($post, array $nodeSettings): bool
    {
        $settingPostIds = $nodeSettings['postQuery']['postId'] ?? [];

        $postId = is_object($post) ? $post->ID : (int) $post;

        // Convert string IDs to integers for comparison
        $settingPostIds = array_map('intval', $settingPostIds);
        $postId = (int) $postId;

        if (! empty($settingPostIds) && ! in_array($postId, $settingPostIds)) {
            $this->logValidationResult(
                false,
                'Post ID does not match configured list',
                is_object($post) ? $post : null,
                ['post_id' => $postId, 'allowed' => $settingPostIds]
            );

            return false;
        }

        return true;
    }

    private function hasValidPostStatus($post, array $nodeSettings): bool
    {
        $settingPostStatus = $nodeSettings['postQuery']['postStatus'] ?? [];

        if (! empty($settingPostStatus) && ! in_array($post->post_status, $settingPostStatus)) {
            $this->logValidationResult(
                false,
                'Post status does not match',
                $post,
                ['post_status' => $post->post_status, 'allowed' => $settingPostStatus]
            );

            return false;
        }

        return true;
    }

    private function hasValidPostAuthor($post, array $nodeSettings): bool
    {
        $settingPostAuthor = $nodeSettings['postQuery']['postAuthor'] ?? [];

        if (empty($settingPostAuthor)) {
            return true;
        }

        $settingPostAuthor = $this->executionContext->resolveExpressionsInArray($settingPostAuthor);

        if (! in_array($post->post_author, $settingPostAuthor)) {
            $this->logValidationResult(
                false,
                'Post author does not match',
                $post,
                ['post_author' => $post->post_author, 'allowed' => $settingPostAuthor]
            );

            return false;
        }

        return true;
    }

    private function hasValidPostTerms($post, array $nodeSettings): bool
    {
        $settingPostTerms = $nodeSettings['postQuery']['postTerms'] ?? [];

        if (empty($settingPostTerms)) {
            return true;
        }

        $settingPostTerms = $this->executionContext->resolveExpressionsInArray($settingPostTerms);

        $groupedSelectedTerms = [];

        foreach ($settingPostTerms as $term) {
            $termParts = explode(':', $term);

            if (count($termParts) !== 2) {
                continue;
            }

            if (! isset($groupedSelectedTerms[$termParts[0]])) {
                $groupedSelectedTerms[$termParts[0]] = [];
            }

            $groupedSelectedTerms[$termParts[0]][] = (int) $termParts[1];
        }

        foreach ($groupedSelectedTerms as $taxonomy => $termIds) {
            $postTerms = wp_get_post_terms($post->ID, $taxonomy, ['fields' => 'ids']);

            if (is_wp_error($postTerms)) {
                $this->logValidationResult(
                    false,
                    'Failed to fetch post terms',
                    $post,
                    ['taxonomy' => $taxonomy, 'error' => $postTerms->get_error_message()]
                );

                return false;
            }

            if (count(array_intersect($postTerms, $termIds)) > 0) {
                return true;
            }
        }

        $this->logValidationResult(
            false,
            'Post has none of the required terms',
            $post,
            ['required' => $groupedSelectedTerms]
        );

        return false;
    }

    /**
     * Log a post query validation failure with context.
     *
     * @param string $reason Human-readable reason for failure
     * @param object|null $post Post object if available
     * @param array<string, mixed> $context Additional context (e.g. post_type, allowed values)
     */
    private function logValidationResult(bool $isValid, string $reason, $post, array $context = []): void
    {
        if (! $this->logger->isDebugEnabled()) {
            return;
        }

        $prefix = $this->getLogPrefix();
        $postInfo = $post !== null && is_object($post) && isset($post->ID, $post->post_type, $post->post_status)
            ? sprintf('Post #%d (post_type: %s, post_status: %s)', $post->ID, $post->post_type, $post->post_status)
            : 'Post unknown';
        $contextStr = '';
        if (! empty($context)) {
            $json = wp_json_encode($context, JSON_UNESCAPED_UNICODE);
            $contextStr = ' ' . str_replace('"', '`', $json);
        }

        if (! $isValid) {
            $this->logger->debug($prefix . 'Post did not match workflow conditions: ' . $reason . '. ' . $postInfo . $contextStr);
            return;
        }

        $this->logger->debug($prefix . 'Post matched workflow conditions: ' . $reason . '. ' . $postInfo . $contextStr);
    }

    private function getLogPrefix(): string
    {
        $workflowId = $this->executionContext->getVariable('global.workflow.id');

        if ($workflowId === null || $workflowId === '') {
            $workflowId = 0;
        }

        return sprintf(self::LOG_PREFIX, $workflowId);
    }
}
