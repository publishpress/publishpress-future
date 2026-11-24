<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use Codeception\Stub;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\SiteResolver;
use Tests\Support\UnitTester;

class SiteResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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
        $resolver = new SiteResolver([]);

        $this->assertEquals('site', $resolver->getType());
    }

    public function testGetValueAsStringReturnsCorrectValue(): void
    {
        $siteResolver = Stub::make(SiteResolver::class, ['getSiteName' => 'Site Name']);

        $this->assertEquals('Site Name', $siteResolver->getValueAsString('name'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyDoesNotExist(): void
    {
        $resolver = new SiteResolver();

        $this->assertEquals('', $resolver->getValueAsString('non_existent_key'));
    }

    public function testGetValueAsStringReturnsSiteNameWhenPropertyIsEmpty(): void
    {
        $resolver = Stub::make(SiteResolver::class, ['getSiteName' => 'Site Name']);

        $this->assertEquals('Site Name', $resolver->getValueAsString());
        $this->assertEquals('Site Name', $resolver->getValueAsString(''));
    }

    public function testIssetReturnsTrueWhenPropertyExists(): void
    {
        $resolver = Stub::make(SiteResolver::class, ['getSiteName' => 'Site Name']);

        $this->assertTrue(isset($resolver->name));
    }

    public function testIssetReturnsFalseWhenPropertyDoesNotExist(): void
    {
        $resolver = Stub::make(SiteResolver::class, ['getSiteName' => 'Site Name']);

        $this->assertFalse(isset($resolver->non_existent_key));
    }

    public function testGetReturnsValueWhenPropertyExists(): void
    {
        $resolver = Stub::make(SiteResolver::class, ['getSiteName' => 'Site Name']);

        $this->assertEquals('Site Name', $resolver->name);
    }

    public function testGetReturnsNullWhenPropertyDoesNotExist(): void
    {
        $resolver = Stub::make(SiteResolver::class, ['getSiteName' => 'Site Name']);

        $this->assertNull($resolver->non_existent_key);
    }

    public function testCompactReturnsCompactedArray(): void
    {
        $resolver = Stub::make(SiteResolver::class, ['getSiteName' => 'Site Name']);

        $this->assertEquals(['type' => 'site', 'value' => 'Site Name'], $resolver->compact());
    }
}
