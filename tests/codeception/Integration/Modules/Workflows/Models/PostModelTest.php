<?php

namespace Tests\Modules\Workflows\Models;

use PublishPress\Future\Modules\Workflows\Models\PostModel;

class PostModelTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    public function setUp(): void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
    }

    public function tearDown(): void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    private function createWorkflows(): array
    {
        $ids = [];

        $ids[] = wp_insert_post([
            'post_title' => 'Test workflow 1',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'workflow'
        ]);

        $ids[] = wp_insert_post([
            'post_title' => 'Test workflow 2',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'workflow'
        ]);

        return $ids;
    }

    public function testSetAndGetManuallyEnabledWorkflows(): void
    {
        $postId = wp_insert_post([
            'post_title' => 'Test post',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'post'
        ]);

        $workflows = $this->createWorkflows();

        $model = new PostModel();
        $model->load($postId);

        $model->setManuallyEnabledWorkflows($workflows);

        $this->assertEquals($workflows, $model->getManuallyEnabledWorkflows());
    }

    public function testAddManuallyEnabledWorkflow(): void
    {
        $postId = wp_insert_post([
            'post_title' => 'Test post',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'post'
        ]);

        $workflows = $this->createWorkflows();

        $model = new PostModel();
        $model->load($postId);

        $model->addManuallyEnabledWorkflow($workflows[0]);
        $this->assertEquals([$workflows[0]], $model->getManuallyEnabledWorkflows());

        $model->addManuallyEnabledWorkflow($workflows[1]);
        $this->assertEquals($workflows, $model->getManuallyEnabledWorkflows());
    }

    public function testRemoveManuallyEnabledWorkflow(): void
    {
        $postId = wp_insert_post([
            'post_title' => 'Test post',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'post'
        ]);

        $workflows = $this->createWorkflows();

        $model = new PostModel();
        $model->load($postId);

        $model->setManuallyEnabledWorkflows($workflows);

        $model->removeManuallyEnabledWorkflow($workflows[0]);
        $this->assertEquals([$workflows[1]], $model->getManuallyEnabledWorkflows());

        $model->removeManuallyEnabledWorkflow($workflows[1]);
        $this->assertEquals([], $model->getManuallyEnabledWorkflows());
    }
}
