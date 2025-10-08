<?php

namespace Tests\Modules\Workflows\Domain\Steps\Triggers\Runners;

use PublishPress\Future\Modules\Workflows\HooksAbstract;

class OnPostPublishRunnerTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    public function setUp(): void
    {
        // Before...
        parent::setUp();

        $this->tester->startWorkflowTriggerExecutionTracking();
    }

    public function tearDown(): void
    {
        $this->tester->stopWorkflowTriggerExecutionTracking();

        parent::tearDown();
    }

    public function testTriggerExecutesWhenDraftPostIsPublishedUsingUpdatePost(): void
    {
        // Create a draft post.
        $postId = wp_insert_post([
            'post_type' => 'post',
            'post_title' => 'Test Post',
            'post_status' => 'draft'
        ]);

        // Creates and starts the workflow.
        $workflowId = $this->tester->haveWorkflowFromFile('on-post-publish-debug-log');
        $this->tester->startWorkflow($workflowId);

        // Publish the created post.
        wp_update_post([
            'ID' => $postId,
            'post_status' => 'publish'
        ]);

        $this->tester->seeATriggerExecuted();

        // Clean up the workflow.
        $this->tester->dontHaveWorkflow($workflowId);
    }

    public function testTriggerExecutesWhenDraftPostIsPublishedUsingPublishPost(): void
    {
        // Create a draft post.
        $postId = wp_insert_post([
            'post_type' => 'post',
            'post_title' => 'Test Post',
            'post_status' => 'draft'
        ]);

        // Creates and starts the workflow.
        $workflowId = $this->tester->haveWorkflowFromFile('on-post-publish-debug-log');
        $this->tester->startWorkflow($workflowId);

        // Publish the created post.
        wp_publish_post($postId);

        $this->tester->seeATriggerExecuted();

        // Clean up the workflow.
        $this->tester->dontHaveWorkflow($workflowId);
    }

    public function testTriggerExecutesDuringPostPublishForFreshPost(): void
    {
        // Creates and starts the workflow
        $workflowId = $this->tester->haveWorkflowFromFile('on-post-publish-debug-log');
        $this->tester->startWorkflow($workflowId);

        // Create a published post
        wp_insert_post([
            'post_type' => 'post',
            'post_title' => 'Test Post 2',
            'post_status' => 'publish'
        ]);

        $this->tester->seeATriggerExecuted();

        // Clean up the workflow.
        $this->tester->dontHaveWorkflow($workflowId);
    }

    public function testAlreadyPublishedPostDoesNotTriggerWorkflow(): void
    {
        // Create a draft post.
        $postId = wp_insert_post([
            'post_type' => 'post',
            'post_title' => 'Test Post',
            'post_status' => 'publish'
        ]);

        // Creates and starts the workflow.
        $workflowId = $this->tester->haveWorkflowFromFile('on-post-publish-debug-log');
        $this->tester->startWorkflow($workflowId);

        // Publish the created post and update it to trigger the hooks twice, always as publish.
        wp_publish_post($postId);
        wp_update_post([
            'ID' => $postId,
            'post_title' => 'Updated Published Post'
        ]);

        $this->tester->seeNoTriggerExecutedForWorkflow($workflowId);

        // Clean up the workflow.
        $this->tester->dontHaveWorkflow($workflowId);
    }

    public function testNonPublishedPostDoesNotTriggerWorkflow(): void
    {
        // Create a draft post.
        $postId = wp_insert_post([
            'post_type' => 'post',
            'post_title' => 'Test Post',
            'post_status' => 'draft'
        ]);

        // Creates and starts the workflow.
        $workflowId = $this->tester->haveWorkflowFromFile('on-post-publish-debug-log');
        $this->tester->startWorkflow($workflowId);

        wp_update_post([
            'ID' => $postId,
            'post_title' => 'Updated Draft Post'
        ]);

        $this->tester->seeNoTriggerExecutedForWorkflow($workflowId);

        // Clean up the workflow.
        $this->tester->dontHaveWorkflow($workflowId);
    }

    public function testPagePublishDoesNotTriggerPostWorkflow(): void
    {
        // Create a draft page
        $pageId = wp_insert_post([
            'post_type' => 'page',
            'post_title' => 'Test Page',
            'post_status' => 'draft'
        ]);

        // Set up workflow for posts
        $postWorkflowId = $this->tester->haveWorkflowFromFile('on-post-publish-debug-log');
        $this->tester->startWorkflow($postWorkflowId);

        // Publish the page
        wp_publish_post($pageId);

        // Verify post workflow wasn't triggered by a page
        $this->tester->seeNoTriggerExecutedForWorkflow($postWorkflowId);

        // Clean up the workflow.
        $this->tester->dontHaveWorkflow($postWorkflowId);
    }
}
