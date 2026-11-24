<?php

namespace PublishPress\Future\Modules\Workflows\Models;

use PublishPress\Future\Modules\Workflows\Domain\Steps\Triggers\Definitions\OnPostWorkflowEnable;
use PublishPress\Future\Modules\Workflows\Module;
use PublishPress\Future\Modules\Workflows\Interfaces\WorkflowsModelInterface;
use WP_Query;

\defined('ABSPATH') || exit;

class WorkflowsModel implements WorkflowsModelInterface
{
    public const OPTION_SAMPLE_WORKFLOWS_CREATED = 'publishpress_future_workflow_samples_created';

    public function getPublishedWorkflowsIds($limit = -1): array
    {
        $args = [
            'post_type' => Module::POST_TYPE_WORKFLOW,
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'fields' => 'ids',
        ];

        $query = new WP_Query($args);

        return $query->posts;
    }

    public function getAllWorkflowsIds(): array
    {
        $args = [
            'post_type' => Module::POST_TYPE_WORKFLOW,
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids',
        ];

        $query = new WP_Query($args);

        return $query->posts;
    }

    public function getPublishedWorkflowsWithLegacyTriggerAsOptions(): array
    {
        return $this->getPublishedWorkflowsWithMetadataAsOptions(WorkflowModel::META_KEY_HAS_LEGACY_TRIGGER, 1);
    }

    public function getPublishedWorkflowsWithManualTrigger($postType = null): array
    {
        $workflows = $this->getPublishedWorkflowsWithMetadataAsOptions(WorkflowModel::META_KEY_HAS_MANUAL_TRIGGER, 1);

        $result = [];
        foreach ($workflows as &$workflow) {
            $workflowModel = new WorkflowModel();
            $workflowModel->load($workflow['value']);

            $triggers = $workflowModel->getTriggerNodes();

            $resultItem = [
                'workflowId' => $workflow['value'],
                'label' => $workflow['label'],
            ];

            $selectedPostTypes = [];

            foreach ($triggers as $trigger) {
                if ($trigger['data']['name'] !== OnPostWorkflowEnable::getNodeTypeName()) {
                    continue;
                }

                if (!empty($trigger['data']['settings']['postQuery']['postType'])) {
                    $selectedPostTypes = array_merge(
                        $selectedPostTypes,
                        $trigger['data']['settings']['postQuery']['postType']
                    );
                }

                $resultItem['label'] = $trigger['data']['settings']['checkboxLabel'] ?? $workflow['label'];
            }

            // Filter by post type
            if (!empty($postType) && !in_array($postType, $selectedPostTypes)) {
                continue;
            }

            $result[] = $resultItem;
        }

        return $result;
    }

    public function getPublishedWorkflowsWithMetadataAsOptions($metaKey, $metaValue): array
    {
        // TODO: Implement another method to filter the workflows without using metaquery. Maybe a custom table?
        $args = [
            'post_type' => Module::POST_TYPE_WORKFLOW,
            'post_status' => 'publish',
            'posts_per_page' => 100,
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
            'meta_query' => [
                [
                    'key' => sanitize_key($metaKey),
                    'value' => (int) $metaValue,
                ],
            ],
        ];

        $query = new WP_Query($args);

        $workflows = [];

        foreach ($query->posts as $workflow) {
            $workflows[] = [
                'value' => $workflow->ID,
                'label' => $workflow->post_title,
            ];
        }

        return $workflows;
    }

    public function hasCreatedSampleWorkflows(): bool
    {
        return (bool) get_option(self::OPTION_SAMPLE_WORKFLOWS_CREATED, false);
    }

    private function setSampleWorkflowsCreated(): void
    {
        update_option(self::OPTION_SAMPLE_WORKFLOWS_CREATED, true);
    }

    public function createSampleWorkflows(array $samples): void
    {
        foreach ($samples as $sample) {
            $workflow = new WorkflowModel();
            $workflow->createNew();
            $workflow->setStatus(WorkflowModel::STATUS_DISABLED);
            $workflow->setTitle($sample['title']);
            $workflow->setDescription($sample['description']);
            $workflow->setFlow(json_decode($sample['flow'], true));
            $workflow->save();
        }

        $this->setSampleWorkflowsCreated();
    }
}
