<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\DatetimeResolver;
use Tests\Support\UnitTester;

class DatetimeResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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
        $resolver = new DatetimeResolver('2021-01-01 00:00:00');

        $this->assertEquals('datetime', $resolver->getType());
    }

    public function testGetValueAsStringReturnsCorrectValue(): void
    {
        $resolver = new DatetimeResolver('2021-01-01 00:00:00');

        $this->assertEquals('2021-01-01 00:00:00', $resolver->getValueAsString());
    }

    public function testIssetReturnsFalse(): void
    {
        $resolver = new DatetimeResolver('2021-01-01 00:00:00');

        $this->assertFalse(isset($resolver->variable));
        $this->assertFalse(isset($resolver->non_existent_property));
    }

    public function testGetReturnsNull(): void
    {
        $resolver = new DatetimeResolver('2021-01-01 00:00:00');

        $this->assertNull($resolver->variable);
        $this->assertNull($resolver->non_existent_property);
    }

    public function testSetDoesNothing(): void
    {
        $resolver = new DatetimeResolver('2021-01-01 00:00:00');

        $resolver->variable = '2021-01-01 00:00:01';
        $resolver->non_existent_property = 'value';

        $this->assertNull($resolver->variable);
    }

    public function testUnsetDoesNothing(): void
    {
        $resolver = new DatetimeResolver('2021-01-01 00:00:00');
    }

    public function test__toStringReturnsCorrectValue(): void
    {
        $resolver = new DatetimeResolver('2021-01-01 00:00:00');

        $this->assertEquals('2021-01-01 00:00:00', (string)$resolver);
    }

    public function testCompactReturnsCompactedArray(): void
    {
        $resolver = new DatetimeResolver('2021-01-01 00:00:00');

        $this->assertEquals(['type' => 'datetime', 'value' => '2021-01-01 00:00:00'], $resolver->compact());
    }
}
