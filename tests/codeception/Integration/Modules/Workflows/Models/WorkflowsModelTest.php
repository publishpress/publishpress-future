<?php

/**
 * Integration tests for published workflow loading and cache invalidation.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace Tests\Modules\Workflows\Models;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowsModel;
use PublishPress\Future\Modules\Workflows\Module;

class WorkflowsModelTest extends WPTestCase
{
    public function testGetPublishedWorkflowsIdsPrimesPostsAndMetadata(): void
    {
        global $wpdb;

        $ids = [];
        for ($index = 0; $index < 10; $index++) {
            $id = $this->createWorkflow('Workflow ' . $index);
            update_post_meta($id, WorkflowModel::META_KEY_DEBUG_RAY_SHOW_QUERIES, '1');
            $ids[] = $id;
        }

        wp_cache_flush();
        $publishedIds = (new WorkflowsModel())->getPublishedWorkflowsIds();
        $this->assertEqualsCanonicalizing($ids, $publishedIds);

        $before = $wpdb->num_queries;
        foreach ($publishedIds as $id) {
            $workflow = new WorkflowModel();
            $this->assertTrue($workflow->load($id));
            $this->assertSame(['nodes' => [], 'edges' => []], $workflow->getFlow());
            $this->assertTrue($workflow->isDebugRayShowQueriesEnabled());
        }

        $this->assertSame($before, $wpdb->num_queries, 'Workflow hydration must use the primed cache.');
    }

    public function testGetPublishedWorkflowsIdsKeepsLimitAndPublishedStatus(): void
    {
        $first = $this->createWorkflow('First', 'publish', '2026-01-01 12:00:00');
        $second = $this->createWorkflow('Second', 'publish', '2026-01-02 12:00:00');
        $this->createWorkflow('Disabled', 'draft');
        $this->createWorkflow('Trashed', 'trash');

        $model = new WorkflowsModel();
        $this->assertSame([$second, $first], $model->getPublishedWorkflowsIds());
        $this->assertSame([$second], $model->getPublishedWorkflowsIds(1));
    }

    public function testGetPublishedWorkflowsIdsReprimesEvictedObjectsOnQueryCacheHit(): void
    {
        global $wpdb;

        $id = $this->createWorkflow('Cached query');
        update_post_meta($id, WorkflowModel::META_KEY_DEBUG_RAY_SHOW_QUERIES, '1');
        $model = new WorkflowsModel();
        $this->assertSame([$id], $model->getPublishedWorkflowsIds());

        wp_cache_delete($id, 'posts');
        wp_cache_delete($id, 'post_meta');
        $this->assertSame([$id], $model->getPublishedWorkflowsIds());

        $before = $wpdb->num_queries;
        $workflow = new WorkflowModel();
        $this->assertTrue($workflow->load($id));
        $this->assertTrue($workflow->isDebugRayShowQueriesEnabled());
        $this->assertSame($before, $wpdb->num_queries);

        update_post_meta($id, WorkflowModel::META_KEY_DEBUG_RAY_SHOW_QUERIES, '0');
        $this->assertSame([$id], $model->getPublishedWorkflowsIds());
        $workflow = new WorkflowModel();
        $this->assertTrue($workflow->load($id));
        $this->assertFalse($workflow->isDebugRayShowQueriesEnabled());
    }

    public function testGetPublishedWorkflowsIdsReflectsEditsAndStatusChanges(): void
    {
        $id = $this->createWorkflow('Original');
        $model = new WorkflowsModel();
        $this->assertSame([$id], $model->getPublishedWorkflowsIds());

        wp_update_post(['ID' => $id, 'post_title' => 'Edited']);
        $this->assertSame([$id], $model->getPublishedWorkflowsIds());
        $workflow = new WorkflowModel();
        $this->assertTrue($workflow->load($id));
        $this->assertSame('Edited', $workflow->getTitle());

        wp_update_post(['ID' => $id, 'post_status' => 'draft']);
        $this->assertSame([], $model->getPublishedWorkflowsIds());

        wp_publish_post($id);
        $this->assertSame([$id], $model->getPublishedWorkflowsIds());

        wp_trash_post($id);
        $this->assertSame([], $model->getPublishedWorkflowsIds());

        wp_untrash_post($id);
        wp_publish_post($id);
        $this->assertSame([$id], $model->getPublishedWorkflowsIds());

        wp_delete_post($id, true);
        $this->assertSame([], $model->getPublishedWorkflowsIds());
    }

    private function createWorkflow(
        string $title,
        string $status = 'publish',
        string $date = '2026-01-01 12:00:00'
    ): int {
        $id = wp_insert_post([
            'post_type' => Module::POST_TYPE_WORKFLOW,
            'post_status' => $status,
            'post_title' => $title,
            'post_date' => $date,
            'post_content' => '{"nodes":[],"edges":[]}',
        ]);
        $this->assertGreaterThan(0, $id);

        return $id;
    }
}
