<?php

namespace PublishPress\Future\Modules\Workflows\Interfaces;

use WP_Post;

interface PostModelInterface
{
    public function load(int $id): bool;

    public function getId(): int;

    public function getTitle(): string;

    public function getValidWorkflowsWithManualTrigger(int $postId): array;

    public function getManuallyEnabledWorkflows(): array;

    public function setManuallyEnabledWorkflows(array $workflowIds): void;

    public function addManuallyEnabledWorkflow(int $workflowId): void;

    public function removeManuallyEnabledWorkflow(int $workflowId): void;

    public function getManuallyEnabledWorkflowsSchedule(int $workflowId): array;


    /**
     * Get the WordPress post object.
     * @return WP_Post|null
     */
    public function getPostObject();
}
