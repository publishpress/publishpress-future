<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\IntegerResolver;
use Tests\Support\UnitTester;

class IntegerResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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
        $resolver = new IntegerResolver(23);

        $this->assertEquals('integer', $resolver->getType());
    }

    public function testGetValueAsStringReturnsCorrectValue(): void
    {
        $resolver = new IntegerResolver(23);

        $this->assertEquals('23', $resolver->getValueAsString());
    }

    public function testGetValueAsStringReturnsZeroWhenZero(): void
    {
        $resolver = new IntegerResolver(0);

        $this->assertEquals('0', $resolver->getValueAsString());
    }

    public function testGetValueAsStringReturnsZeroWhenNegativeZero(): void
    {
        $resolver = new IntegerResolver(-0);

        $this->assertEquals('0', $resolver->getValueAsString());
    }

    public function testGetValueAsStringReturnsCorrectValueWhenNegative(): void
    {
        $resolver = new IntegerResolver(-23);

        $this->assertEquals('-23', $resolver->getValueAsString());
    }

    public function testGetValueAsStringReturnsCorrectValueWhenPositive(): void
    {
        $resolver = new IntegerResolver(23);

        $this->assertEquals('23', $resolver->getValueAsString());
    }

    public function testIssetReturnsFalse(): void
    {
        $resolver = new IntegerResolver(23);

        $this->assertFalse(isset($resolver->variable));
        $this->assertFalse(isset($resolver->non_existent_property));
    }

    public function testGetReturnsNull(): void
    {
        $resolver = new IntegerResolver(23);

        $this->assertNull($resolver->variable);
        $this->assertNull($resolver->non_existent_property);
    }

    public function testSetDoesNothing(): void
    {
        $resolver = new IntegerResolver(23);

        $resolver->variable = 24;

        $this->assertNull($resolver->variable);
        $this->assertEquals('23', $resolver->getValueAsString());
    }

    public function testUnsetDoesNothing(): void
    {
        $resolver = new IntegerResolver(23);

        unset($resolver->variable);

        $this->assertEquals('23', $resolver->getValueAsString());
    }

    public function test__toStringReturnsCorrectValue(): void
    {
        $resolver = new IntegerResolver(23);

        $this->assertEquals('23', (string)$resolver);
    }

    public function testCompactReturnsCompactedArray(): void
    {
        $resolver = new IntegerResolver(34);

        $this->assertEquals(['type' => 'integer', 'value' => 34], $resolver->compact());
    }
}
