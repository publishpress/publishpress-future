<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\WorkflowMetaResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\WorkflowResolver;
use PublishPress\Future\Modules\Workflows\Models\WorkflowModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowsModel;

class WorkflowMetaResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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

    public function testGetTypeReturnsCorrectType(): void
    {
        $resolver = new WorkflowMetaResolver(34);

        $this->assertEquals('workflow_meta', $resolver->getType());
    }

    public function testGetValueAsStringReturnsCorrectValue(): void
    {
        $workflowId = $this->factory()->post->create([
            'post_type' => 'ppfuture_workflow',
            'post_title' => 'Workflow Name',
            'post_content' => 'Workflow description',
            'post_status' => 'publish',
        ]);

        $resolver = new WorkflowMetaResolver($workflowId);

        update_post_meta($workflowId, 'custom_meta', 'this is a custom meta');

        $this->assertEquals('this is a custom meta', $resolver->getValueAsString('custom_meta'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyNotExists(): void
    {
        $workflowId = $this->factory()->post->create([
            'post_type' => 'ppfuture_workflow',
            'post_title' => 'Workflow Name',
            'post_content' => 'Workflow description',
            'post_status' => 'publish',
        ]);

        $resolver = new WorkflowMetaResolver($workflowId);

        $this->assertEquals('', $resolver->getValueAsString('non_existent_property'));
    }

    public function testIssetReturnsTrueWhenPropertyExists(): void
    {
        $workflowId = $this->factory()->post->create([
            'post_type' => 'ppfuture_workflow',
            'post_title' => 'Workflow Name',
            'post_content' => 'Workflow description',
            'post_status' => 'publish',
        ]);

        $resolver = new WorkflowMetaResolver($workflowId);

        update_post_meta($workflowId, 'custom_meta_1', 'this is a custom meta');
        update_post_meta($workflowId, 'custom_meta_2', 'this is another custom meta');

        $this->assertTrue(isset($resolver->custom_meta_1));
        $this->assertTrue(isset($resolver->custom_meta_2));
    }

    public function testIssetReturnsFalseWhenPropertyDoesNotExist(): void
    {
        $workflowId = $this->factory()->post->create([
            'post_type' => 'ppfuture_workflow',
            'post_title' => 'Workflow Name',
            'post_content' => 'Workflow description',
            'post_status' => 'publish',
        ]);

        $resolver = new WorkflowMetaResolver($workflowId);

        update_post_meta($workflowId, 'custom_meta_1', 'this is a custom meta');
        update_post_meta($workflowId, 'custom_meta_2', 'this is another custom meta');

        $this->assertTrue(isset($resolver->custom_meta_1));
        $this->assertTrue(isset($resolver->custom_meta_2));

        $this->assertFalse(isset($resolver->non_existent_property));
    }

    public function testGetReturnsCorrectValueWhenPropertyExists(): void
    {
        $workflowId = $this->factory()->post->create([
            'post_type' => 'ppfuture_workflow',
            'post_title' => 'Workflow Name',
            'post_content' => 'Workflow description',
            'post_status' => 'publish',
        ]);

        $resolver = new WorkflowMetaResolver($workflowId);

        update_post_meta($workflowId, 'custom_meta_1', 'this is a custom meta');
        update_post_meta($workflowId, 'custom_meta_2', 'this is another custom meta');

        $this->assertEquals('this is a custom meta', $resolver->custom_meta_1);
        $this->assertEquals('this is another custom meta', $resolver->custom_meta_2);
    }

    public function testGetReturnsEmptyStringWhenPropertyDoesNotExist(): void
    {
        $workflowId = $this->factory()->post->create([
            'post_type' => 'ppfuture_workflow',
            'post_title' => 'Workflow Name',
            'post_content' => 'Workflow description',
            'post_status' => 'publish',
        ]);

        $resolver = new WorkflowMetaResolver($workflowId);

        update_post_meta($workflowId, 'custom_meta_1', 'this is a custom meta');
        update_post_meta($workflowId, 'custom_meta_2', 'this is another custom meta');

        $this->assertEquals('', $resolver->non_existent_property);
    }

    public function testSetUpdatesExistentProperty(): void
    {
        $workflowId = $this->factory()->post->create([
            'post_type' => 'ppfuture_workflow',
            'post_title' => 'Workflow Name',
            'post_content' => 'Workflow description',
            'post_status' => 'publish',
        ]);

        $resolver = new WorkflowMetaResolver($workflowId);

        update_post_meta($workflowId, 'custom_meta_1', 'this is a custom meta');
        update_post_meta($workflowId, 'custom_meta_2', 'this is another custom meta');

        $resolver->setValue('custom_meta_1', 'this is a new custom meta');
        $resolver->setValue('custom_meta_2', 'this is another new custom meta');

        $this->assertEquals('this is a new custom meta', $resolver->custom_meta_1);
        $this->assertEquals('this is another new custom meta', $resolver->custom_meta_2);
    }

    public function testUnsetDoesNothing(): void
    {
        $workflowId = $this->factory()->post->create([
            'post_type' => 'ppfuture_workflow',
            'post_title' => 'Workflow Name',
            'post_content' => 'Workflow description',
            'post_status' => 'publish',
        ]);

        $resolver = new WorkflowMetaResolver($workflowId);

        update_post_meta($workflowId, 'custom_meta_1', 'this is a custom meta');
        update_post_meta($workflowId, 'custom_meta_2', 'this is another custom meta');

        unset($resolver->custom_meta_1);
        unset($resolver->custom_meta_2);

        $this->assertEquals('this is a custom meta', $resolver->custom_meta_1);
        $this->assertEquals('this is another custom meta', $resolver->custom_meta_2);
    }

    public function testToStringReturnsCorrectValue(): void
    {
        $workflowId = $this->factory()->post->create([
            'post_type' => 'ppfuture_workflow',
            'post_title' => 'Workflow Name',
            'post_content' => 'Workflow description',
            'post_status' => 'publish',
        ]);

        $resolver = new WorkflowMetaResolver($workflowId);

        $this->assertEquals('workflow_meta', (string) $resolver);
    }

    public function testCompactReturnsCompactedValue(): void
    {
        $workflowId = $this->factory()->post->create([
            'post_type' => 'ppfuture_workflow',
            'post_title' => 'Workflow Name',
            'post_content' => 'Workflow description',
            'post_status' => 'publish',
        ]);

        $resolver = new WorkflowMetaResolver($workflowId);

        update_post_meta($workflowId, 'custom_meta_1', 'this is a custom meta');
        update_post_meta($workflowId, 'custom_meta_2', 'this is another custom meta');

        $this->assertEquals([
            'type' => 'workflow_meta',
            'value' => 'this is a custom meta',
        ], $resolver->compact('custom_meta_1'));
        $this->assertEquals([
            'type' => 'workflow_meta',
            'value' => 'this is another custom meta',
        ], $resolver->compact('custom_meta_2'));
    }
}
