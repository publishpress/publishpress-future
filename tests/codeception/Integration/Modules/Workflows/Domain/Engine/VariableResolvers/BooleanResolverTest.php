<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\BooleanResolver;
use Tests\Support\UnitTester;

class BooleanResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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
        $resolver = new BooleanResolver(true);

        $this->assertEquals('boolean', $resolver->getType());
    }

    public function testGetValueAsStringReturnsYesWhenTrue(): void
    {
        $resolver = new BooleanResolver(true);

        $this->assertEquals('Yes', $resolver->getValueAsString());
    }

    public function testGetValueAsStringReturnsNoWhenFalse(): void
    {
        $resolver = new BooleanResolver(false);

        $this->assertEquals('No', $resolver->getValueAsString());
    }

    public function testIssetReturnsFalse(): void
    {
        $resolver = new BooleanResolver(true);

        $this->assertFalse(isset($resolver->variable));
        $this->assertFalse(isset($resolver->non_existent_property));
    }

    public function testGetReturnsNull(): void
    {
        $resolver = new BooleanResolver(true);

        $this->assertNull($resolver->variable);
        $this->assertNull($resolver->non_existent_property);
    }

    public function testSetDoesNothing(): void
    {
        $resolver = new BooleanResolver(true);

        $resolver->variable = false;
        $resolver->non_existent_property = 'value';

        $this->assertNull($resolver->variable);
    }

    public function testUnsetDoesNothing(): void
    {
        $resolver = new BooleanResolver(true);

        unset($resolver->variable);
        unset($resolver->non_existent_property);

        $this->assertNull($resolver->variable);
        $this->assertFalse(isset($resolver->non_existent_property));
    }

    public function testToStringReturnsYesWhenTrue(): void
    {
        $resolver = new BooleanResolver(true);

        $this->assertEquals('Yes', (string)$resolver);
    }

    public function testCompactReturnsCompactedArray(): void
    {
        $resolver = new BooleanResolver(true);

        $this->assertEquals(['type' => 'boolean', 'value' => true], $resolver->compact());
    }
}
