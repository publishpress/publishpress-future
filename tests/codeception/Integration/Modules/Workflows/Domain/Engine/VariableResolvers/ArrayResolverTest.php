<?php

namespace Tests\Modules\Workflows\Domain\Engine\VaiableResolvers;

use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\ArrayResolver;

class ArrayResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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
        $resolver = new ArrayResolver(['key' => 'value']);

        $this->assertEquals('array', $resolver->getType());
    }

    public function testGetValueAsStringReturnsCorrectValue(): void
    {
        $resolver = new ArrayResolver(['key' => 'value']);

        $this->assertEquals('value', $resolver->getValueAsString('key'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyDoesNotExist(): void
    {
        $resolver = new ArrayResolver(['key' => 'value']);

        $this->assertEquals('', $resolver->getValueAsString('non_existent_key'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyIsNull(): void
    {
        $resolver = new ArrayResolver(['key' => null]);

        $this->assertEquals('', $resolver->getValueAsString('key'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyIsEmptyString(): void
    {
        $resolver = new ArrayResolver(['key' => '']);

        $this->assertEquals('', $resolver->getValueAsString('key'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyIsFalse(): void
    {
        $resolver = new ArrayResolver(['key' => false]);

        $this->assertEquals('', $resolver->getValueAsString('key'));
    }

    public function testGetValueAsStringReturnsZeroStringWhenPropertyIsZero(): void
    {
        $resolver = new ArrayResolver(['key' => 0]);

        $this->assertEquals('0', $resolver->getValueAsString('key'));
    }

    public function testIssetReturnsTrueWhenPropertyExists(): void
    {
        $resolver = new ArrayResolver(['key' => 'value']);

        $this->assertTrue(isset($resolver->key));
    }

    public function testIssetReturnsFalseWhenPropertyDoesNotExist(): void
    {
        $resolver = new ArrayResolver(['key' => 'value']);

        $this->assertFalse(isset($resolver->non_existent_key));
    }

    public function testGetReturnsCorrectValueWhenPropertyExists(): void
    {
        $resolver = new ArrayResolver(['key' => 'value']);

        $this->assertEquals('value', $resolver->key);
    }

    public function testGetReturnsNullWhenPropertyDoesNotExist(): void
    {
        $resolver = new ArrayResolver(['key' => 'value']);

        $this->assertNull($resolver->non_existent_key);
    }

    public function testSetSetsValue(): void
    {
        $resolver = new ArrayResolver(['key' => 'value']);

        $resolver->key = 'new_value';

        $this->assertEquals('value', $resolver->key);
    }

    public function testUnsetRemovesProperty(): void
    {
        $resolver = new ArrayResolver(['key' => 'value']);

        unset($resolver->key);

        $this->assertNotNull($resolver->key);
    }

    public function testToStringReturnsJsonEncodedArray(): void
    {
        $resolver = new ArrayResolver(['key' => 'value']);

        $this->assertEquals('{"key":"value"}', (string)$resolver);
    }

    public function testToStringReturnsEmptyJsonWhenArrayIsEmpty(): void
    {
        $resolver = new ArrayResolver([]);

        $this->assertEquals('[]', (string)$resolver);
    }

    public function testCompactReturnsCompactedArray(): void
    {
        $resolver = new ArrayResolver(['key' => 'value']);

        $this->assertEquals(['type' => 'array', 'value' => ['key' => 'value']], $resolver->compact());
    }
}
