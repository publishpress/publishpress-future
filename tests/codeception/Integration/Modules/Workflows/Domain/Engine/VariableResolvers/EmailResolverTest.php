<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\EmailResolver;
use Tests\Support\UnitTester;

class EmailResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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
        $resolver = new EmailResolver('a@a.com');

        $this->assertEquals('email', $resolver->getType());
    }

    public function testGetValueAsStringReturnsCorrectValue(): void
    {
        $resolver = new EmailResolver('a@a.com');

        $this->assertEquals('a@a.com', $resolver->getValueAsString());
    }

    public function testIssetReturnsFalse(): void
    {
        $resolver = new EmailResolver('a@a.com');

        $this->assertFalse(isset($resolver->variable));
        $this->assertFalse(isset($resolver->non_existent_property));
    }

    public function testGetReturnsNull(): void
    {
        $resolver = new EmailResolver('a@a.com');

        $this->assertNull($resolver->variable);
        $this->assertNull($resolver->non_existent_property);
    }

    public function testSetDoesNothing(): void
    {
        $resolver = new EmailResolver('a@a.com');

        $resolver->variable = 'b@a.com';

        $this->assertNull($resolver->variable);
        $this->assertEquals('a@a.com', $resolver->getValueAsString());
    }

    public function testUnsetDoesNothing(): void
    {
        $resolver = new EmailResolver('a@a.com');

        unset($resolver->variable);

        $this->assertEquals('a@a.com', $resolver->getValueAsString());
    }

    public function test__toStringReturnsCorrectValue(): void
    {
        $resolver = new EmailResolver('a@a.com');

        $this->assertEquals('a@a.com', (string)$resolver);
    }

    public function testCompactReturnsCompactedArray(): void
    {
        $resolver = new EmailResolver('a@a.com');

        $this->assertEquals(['type' => 'email', 'value' => 'a@a.com'], $resolver->compact());
    }
}
