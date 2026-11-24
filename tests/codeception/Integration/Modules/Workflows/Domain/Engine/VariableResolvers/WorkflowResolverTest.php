<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\NodeResolver;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\WorkflowResolver;
use Tests\Support\UnitTester;

class WorkflowResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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
        $resolver = new WorkflowResolver([
            'id' => 34,
            'title' => 'Workflow Name',
            'description' => 'Workflow description',
            'modified_at' => '2021-01-01 00:00:00',
            'execution_id' => '0000-0129-af10-a001',
            'execution_trace' => ['step1', 'step2'],
        ]);

        $this->assertEquals('workflow', $resolver->getType());
    }

    public function testGetValueAsStringReturnsCorrectValue(): void
    {
        $resolver = new WorkflowResolver([
            'id' => 34,
            'title' => 'Workflow Name',
            'description' => 'Workflow description',
            'modified_at' => '2021-01-01 00:00:00',
            'execution_id' => '0000-0129-af10-a001',
            'execution_trace' => ['step1', 'step2'],
        ]);

        $this->assertEquals('34', $resolver->getValueAsString('ID'));
        $this->assertEquals('34', $resolver->getValueAsString('id'));
        $this->assertEquals('Workflow Name', $resolver->getValueAsString('title'));
        $this->assertEquals('Workflow description', $resolver->getValueAsString('description'));
        $this->assertEquals('2021-01-01 00:00:00', $resolver->getValueAsString('modified_at'));
        $this->assertEquals('0000-0129-af10-a001', $resolver->getValueAsString('execution_id'));
        $this->assertEquals('step1, step2', $resolver->getValueAsString('execution_trace'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyNotExists(): void
    {
        $resolver = new WorkflowResolver([
            'id' => 34,
            'title' => 'Workflow Name',
            'description' => 'Workflow description',
            'modified_at' => '2021-01-01 00:00:00',
            'execution_id' => '0000-0129-af10-a001',
            'execution_trace' => ['step1', 'step2'],
        ]);

        $this->assertEquals('', $resolver->getValueAsString('non_existent_property'));
    }

    public function testIssetReturnsTrueWhenPropertyExists(): void
    {
        $resolver = new WorkflowResolver([
            'id' => 34,
            'title' => 'Workflow Name',
            'description' => 'Workflow description',
            'modified_at' => '2021-01-01 00:00:00',
            'execution_id' => '0000-0129-af10-a001',
            'execution_trace' => ['step1', 'step2'],
        ]);

        $this->assertTrue(isset($resolver->ID));
        $this->assertTrue(isset($resolver->id));
        $this->assertTrue(isset($resolver->title));
        $this->assertTrue(isset($resolver->description));
        $this->assertTrue(isset($resolver->modified_at));
        $this->assertTrue(isset($resolver->execution_id));
        $this->assertTrue(isset($resolver->execution_trace));
    }

    public function testIssetReturnsFalseWhenPropertyDoesNotExist(): void
    {
        $resolver = new WorkflowResolver([
            'id' => 34,
            'title' => 'Workflow Name',
            'description' => 'Workflow description',
            'modified_at' => '2021-01-01 00:00:00',
            'execution_id' => '0000-0129-af10-a001',
            'execution_trace' => ['step1', 'step2'],
        ]);

        $this->assertFalse(isset($resolver->non_existent_property));
    }

    public function testGetReturnsCorrectValueWhenPropertyExists(): void
    {
        $resolver = new WorkflowResolver([
            'id' => 34,
            'title' => 'Workflow Name',
            'description' => 'Workflow description',
            'modified_at' => '2021-01-01 00:00:00',
            'execution_id' => '0000-0129-af10-a001',
            'execution_trace' => ['step1', 'step2'],
        ]);

        $this->assertEquals(34, $resolver->ID);
        $this->assertEquals('Workflow Name', $resolver->title);
        $this->assertEquals('Workflow description', $resolver->description);
        $this->assertEquals('2021-01-01 00:00:00', $resolver->modified_at);
        $this->assertEquals('0000-0129-af10-a001', $resolver->execution_id);
        $this->assertEquals(['step1', 'step2'], $resolver->execution_trace);
    }

    public function testGetReturnsNullWhenPropertyDoesNotExist(): void
    {
        $resolver = new WorkflowResolver([
            'id' => 34,
            'title' => 'Workflow Name',
            'description' => 'Workflow description',
            'modified_at' => '2021-01-01 00:00:00',
            'execution_id' => '0000-0129-af10-a001',
            'execution_trace' => ['step1', 'step2'],
        ]);

        $this->assertNull($resolver->non_existent_property);
    }

    public function testSetUpdatesExistentProperty(): void
    {
        $resolver = new WorkflowResolver([
            'id' => 34,
            'title' => 'Workflow Name',
            'description' => 'Workflow description',
            'modified_at' => '2021-01-01 00:00:00',
            'execution_id' => '0000-0129-af10-a001',
            'execution_trace' => ['step1', 'step2'],
        ]);

        $resolver->setValue('id', 35);
        $resolver->setValue('title', 'New Workflow Name');
        $resolver->setValue('description', 'New Workflow description');
        $resolver->setValue('modified_at', '2021-01-02 00:00:00');
        $resolver->setValue('execution_id', '0000-0129-af10-a002');
        $resolver->setValue('execution_trace', ['step1', 'step2', 'step3']);

        $this->assertEquals(35, $resolver->ID);
        $this->assertEquals('New Workflow Name', $resolver->title);
        $this->assertEquals('New Workflow description', $resolver->description);
        $this->assertEquals('2021-01-02 00:00:00', $resolver->modified_at);
        $this->assertEquals('0000-0129-af10-a002', $resolver->execution_id);
        $this->assertEquals(['step1', 'step2', 'step3'], $resolver->execution_trace);
    }

    public function testSetUpDoesNotUpdateNonExistentProperty(): void
    {
        $resolver = new WorkflowResolver([
            'id' => 34,
        ]);

        $resolver->non_existent_property = 'New Value';

        $this->assertEquals(34, $resolver->ID);
        $this->assertNull($resolver->non_existent_property);
    }

    public function testUnsetDoesNothing(): void
    {
        $resolver = new WorkflowResolver([
            'id' => 34,
            'title' => 'Workflow Name',
            'description' => 'Workflow description',
            'modified_at' => '2021-01-01 00:00:00',
            'execution_id' => '0000-0129-af10-a001',
            'execution_trace' => ['step1', 'step2'],
        ]);

        unset($resolver->id);

        $this->assertEquals(34, $resolver->ID);
        $this->assertEquals('Workflow Name', $resolver->title);
        $this->assertEquals('Workflow description', $resolver->description);
        $this->assertEquals('2021-01-01 00:00:00', $resolver->modified_at);
        $this->assertEquals('0000-0129-af10-a001', $resolver->execution_id);
    }

    public function testToStringReturnsCorrectValue(): void
    {
        $resolver = new WorkflowResolver([
            'id' => 34,
            'title' => 'Workflow Name',
            'description' => 'Workflow description',
            'modified_at' => '2021-01-01 00:00:00',
            'execution_id' => '0000-0129-af10-a001',
            'execution_trace' => ['step1', 'step2'],
        ]);

        $this->assertEquals('{"title":"Workflow Name","description":"Workflow description","modified_at":"2021-01-01 00:00:00","execution_id":"0000-0129-af10-a001","execution_trace":["step1","step2"],"ID":34}', (string) $resolver);
    }

    public function testCompactReturnsCompactedArray(): void
    {
        $resolver = new WorkflowResolver([
            'id' => 34,
            'title' => 'Workflow Name',
            'description' => 'Workflow description',
            'modified_at' => '2021-01-01 00:00:00',
            'execution_id' => '0000-0129-af10-a001',
            'execution_trace' => ['step1', 'step2'],
        ]);

        $this->assertEquals(['type' => 'workflow', 'value' => 34, 'execution_id' => '0000-0129-af10-a001', 'execution_trace' => 'step1, step2'], $resolver->compact());
    }
}
