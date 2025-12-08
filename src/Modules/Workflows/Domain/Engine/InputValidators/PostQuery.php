<?php

namespace PublishPress\Future\Modules\Workflows\Domain\Engine\InputValidators;

use PublishPress\Future\Modules\Workflows\Interfaces\InputValidatorsInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\ExecutionContextInterface;
use PublishPress\Future\Modules\Workflows\Interfaces\JsonLogicEngineInterface;
use PublishPress\Future\Modules\Workflows\Module;

class PostQuery implements InputValidatorsInterface
{
    private ExecutionContextInterface $executionContext;

    private JsonLogicEngineInterface $jsonLogicEngine;

    public function __construct(
        ExecutionContextInterface $executionContext,
        JsonLogicEngineInterface $jsonLogicEngine
    ) {
        $this->executionContext = $executionContext;
        $this->jsonLogicEngine = $jsonLogicEngine;
    }

    public function validate(array $args): bool
    {
        $post = $args['post'];
        $node = $args['node'];
        $nodeSettings = $node['data']['settings'] ?? [];

        if ($this->isLegacyPostQuery($nodeSettings)) {
            return $this->validateLegacyPostQuery($post, $nodeSettings);
        }

        return $this->validateJsonPostQuery($nodeSettings);
    }

    private function validateLegacyPostQuery($post, array $nodeSettings)
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
            return false;
        }

        return true;
    }

    private function validateJsonPostQuery(array $nodeSettings)
    {
        $json = $nodeSettings['postQuery']['json'] ?? [];

        if (empty($json)) {
            return false;
        }

        $json = $this->executionContext->resolveExpressionsInJsonLogic($json);

        $result = $this->jsonLogicEngine->apply($json, []);

        if (! is_bool($result)) {
            return false;
        }

        return $result;
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

    private function hasValidPostType($post, array $nodeSettings)
    {
        // Prevent to apply actions to workflows
        if ($post->post_type === Module::POST_TYPE_WORKFLOW) {
            return false;
        }

        $settingPostTypes = $nodeSettings['postQuery']['postType'] ?? [];

        // Invalidate nodes that don't specify a post type to avoid applying actions to all post types
        if (empty($settingPostTypes)) {
            return false;
        }

        if (!empty($settingPostTypes) && !in_array($post->post_type, $settingPostTypes)) {
            return false;
        }

        return true;
    }

    private function hasValidPostId($postId, array $nodeSettings)
    {
        $settingPostIds = $nodeSettings['postQuery']['postId'] ?? [];

        if (is_object($postId)) {
            $postId = $postId->ID;
        }

        // Convert string IDs to integers for comparison
        $settingPostIds = array_map('intval', $settingPostIds);
        $postId = (int) $postId;

        if (!empty($settingPostIds) && !in_array($postId, $settingPostIds)) {
            return false;
        }

        return true;
    }

    private function hasValidPostStatus($post, array $nodeSettings)
    {
        $settingPostStatus = $nodeSettings['postQuery']['postStatus'] ?? [];

        if (!empty($settingPostStatus) && !in_array($post->post_status, $settingPostStatus)) {
            return false;
        }

        return true;
    }

    private function hasValidPostAuthor($post, array $nodeSettings)
    {
        $settingPostAuthor = $nodeSettings['postQuery']['postAuthor'] ?? [];

        if (empty($settingPostAuthor)) {
            return true;
        }

        $settingPostAuthor = $this->executionContext->resolveExpressionsInArray($settingPostAuthor);

        return in_array($post->post_author, $settingPostAuthor);
    }

    private function hasValidPostTerms($post, array $nodeSettings)
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

            if (!isset($groupedSelectedTerms[$termParts[0]])) {
                $groupedSelectedTerms[$termParts[0]] = [];
            }

            $groupedSelectedTerms[$termParts[0]][] = (int)$termParts[1];
        }

        foreach ($groupedSelectedTerms as $taxonomy => $termIds) {
            $postTerms = wp_get_post_terms($post->ID, $taxonomy, ['fields' => 'ids']);

            if (is_wp_error($postTerms)) {
                return false;
            }

            if (count(array_intersect($postTerms, $termIds)) > 0) {
                return true;
            }
        }

        return false;
    }
}
