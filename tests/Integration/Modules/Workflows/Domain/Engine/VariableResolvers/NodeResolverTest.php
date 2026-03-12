<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\NodeResolver;
use Tests\Support\UnitTester;

class NodeResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertEquals('node', $resolver->getType());
    }

    public function testGetValueAsStringReturnsCorrectValue(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertEquals('34', $resolver->getValueAsString('id'));
        $this->assertEquals('Node Name', $resolver->getValueAsString('name'));
        $this->assertEquals('Node Label', $resolver->getValueAsString('label'));
        $this->assertEquals('2021-01-01 00:00:00', $resolver->getValueAsString('activation_timestamp'));
        $this->assertEquals('nodeSlug', $resolver->getValueAsString('slug'));
        $this->assertEquals('0', $resolver->getValueAsString('postId'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyNotExists(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertEquals('', $resolver->getValueAsString('non_existent_property'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyIsNull(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertEquals('', $resolver->getValueAsString('non_existent_property'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyIsEmptyString(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertEquals('', $resolver->getValueAsString(''));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyIsFalse(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertEquals('', $resolver->getValueAsString(false));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyIsZero(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
        ]);

        $this->assertEquals('', $resolver->getValueAsString(0));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyIsZeroString(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertEquals('', $resolver->getValueAsString('0'));
    }

    public function testIssetReturnsTrueWhenPropertyExists(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertTrue(isset($resolver->ID));
        $this->assertTrue(isset($resolver->name));
        $this->assertTrue(isset($resolver->label));
        $this->assertTrue(isset($resolver->activation_timestamp));
        $this->assertTrue(isset($resolver->postId));
    }

    public function testIssetReturnsFalseWhenPropertyDoesNotExist(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertFalse(isset($resolver->non_existent_property));
    }

    public function testGetReturnsCorrectValueWhenPropertyExists(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertEquals(34, $resolver->ID);
        $this->assertEquals('Node Name', $resolver->name);
        $this->assertEquals('Node Label', $resolver->label);
        $this->assertEquals('2021-01-01 00:00:00', $resolver->activation_timestamp);
        $this->assertEquals('nodeSlug', $resolver->slug);
        $this->assertEquals('0', $resolver->postId);
    }

    public function testGetReturnsNullWhenPropertyDoesNotExist(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertNull($resolver->non_existent_property);
    }

    public function testSetSetsValueDoNotChangeTheValues(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $resolver->ID = 35;
        $resolver->name = 'New Node Name';
        $resolver->label = 'New Node Label';
        $resolver->activation_timestamp = '2021-01-02 00:00:00';

        $this->assertEquals(34, $resolver->ID);
        $this->assertEquals('Node Name', $resolver->name);
        $this->assertEquals('Node Label', $resolver->label);
        $this->assertEquals('2021-01-01 00:00:00', $resolver->activation_timestamp);
        $this->assertEquals('0', $resolver->postId);
    }

    public function testUnsetRemovesProperty(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        unset($resolver->ID);
        unset($resolver->name);
        unset($resolver->label);
        unset($resolver->activation_timestamp);
        unset($resolver->postId);

        $this->assertNotNull($resolver->ID);
        $this->assertNotNull($resolver->name);
        $this->assertNotNull($resolver->label);
        $this->assertNotNull($resolver->activation_timestamp);
    }

    public function testToStringReturnsJsonEncodedArray(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertEquals('{"ID":34,"name":"Node Name","label":"Node Label","activation_timestamp":"2021-01-01 00:00:00","slug":"nodeSlug"}', (string)$resolver);
    }

    public function testToStringReturnsEmptyJsonWhenArrayIsEmpty(): void
    {
        $resolver = new NodeResolver([]);

        $this->assertEquals('[]', (string)$resolver);
    }

    public function testCompactReturnsCompactedArray(): void
    {
        $resolver = new NodeResolver([
            'id' => '34',
            'name' => 'Node Name',
            'label' => 'Node Label',
            'activation_timestamp' => '2021-01-01 00:00:00',
            'slug' => 'nodeSlug',
            'postId' => 0,
        ]);

        $this->assertEquals(
            [
                'type' => 'node',
                'value' => [
                    'id' => '34',
                    'name' => 'Node Name',
                    'label' => 'Node Label',
                    'activation_timestamp' => '2021-01-01 00:00:00',
                    'slug' => 'nodeSlug',
                    'postId' => 0,
                ]
            ],
            $resolver->compact()
        );
    }
}
