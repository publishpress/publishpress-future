<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\StringResolver;
use Tests\Support\UnitTester;

class StringResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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
        $resolver = new StringResolver(true);

        $this->assertEquals('string', $resolver->getType());
    }

    public function testGetValueAsStringReturnsCorrectValue(): void
    {
        $stringResolver = new StringResolver('String Value');

        $this->assertEquals('String Value', $stringResolver->getValueAsString());
    }

    public function testIssetReturnsFalse(): void
    {
        $stringResolver = new StringResolver('String Value');

        $this->assertFalse(isset($stringResolver->variable));
        $this->assertFalse(isset($stringResolver->non_existent_property));
    }

    public function testGetReturnsNull(): void
    {
        $stringResolver = new StringResolver('String Value');

        $this->assertNull($stringResolver->variable);
        $this->assertNull($stringResolver->non_existent_property);
    }

    public function testSetDoesNothing(): void
    {
        $stringResolver = new StringResolver('String Value');

        $stringResolver->variable = 'New String Value';

        $this->assertNull($stringResolver->variable);
        $this->assertEquals('String Value', $stringResolver->getValueAsString());
    }

    public function testUnsetDoesNothing(): void
    {
        $stringResolver = new StringResolver('String Value');

        unset($stringResolver->variable);

        $this->assertEquals('String Value', $stringResolver->getValueAsString());
    }

    public function test__toStringReturnsCorrectValue(): void
    {
        $stringResolver = new StringResolver('String Value');

        $this->assertEquals('String Value', (string) $stringResolver);
    }

    public function testCompactReturnsCompactedArray(): void
    {
        $resolver = new StringResolver('String Value');

        $this->assertEquals(['type' => 'string', 'value' => 'String Value'], $resolver->compact());
    }
}
