<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\PostResolver;
use stdClass;
use Tests\Support\UnitTester;

class PostResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var \Closure
     */
    private $expirablePostModelFactory;

    public function setUp(): void
    {
        // Before...
        parent::setUp();

        // Your set up methods here.
        $this->expirablePostModelFactory = function ($postId) {
            return new stdClass();
        };
    }

    public function tearDown(): void
    {
        // Your tear down methods here.

        // Then...
        parent::tearDown();
    }

    public function testGetTypeReturnsCorrectType(): void
    {
        $hooks = new HooksFacade();

        $resolver = new PostResolver(new stdClass(), $hooks, '', $this->expirablePostModelFactory);

        $this->assertEquals('post', $resolver->getType());
    }

    public function testGetValueAsStringReturnsCorrectValue(): void
    {
        $post = new stdClass();
        $post->ID = 23;

        $hooks = new HooksFacade();

        $resolver = new PostResolver($post, $hooks, '', $this->expirablePostModelFactory);

        $this->assertEquals('23', $resolver->getValueAsString('ID'));
        $this->assertEquals('23', $resolver->getValueAsString('id'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyDoesNotExist(): void
    {
        $post = new stdClass();

        $hooks = new HooksFacade();

        $resolver = new PostResolver($post, $hooks, '', $this->expirablePostModelFactory);

        $this->assertEquals('', $resolver->getValueAsString('non_existent_key'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyIsNull(): void
    {
        $post = new stdClass();
        $post->ID = null;

        $hooks = new HooksFacade();

        $resolver = new PostResolver($post, $hooks, '', $this->expirablePostModelFactory);

        $this->assertEquals('', $resolver->getValueAsString('ID'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyIsEmptyString(): void
    {
        $post = new stdClass();
        $post->ID = '';

        $hooks = new HooksFacade();

        $resolver = new PostResolver($post, $hooks, '', $this->expirablePostModelFactory);

        $this->assertEquals('', $resolver->getValueAsString('ID'));
    }

    public function testGetValueAsStringReturnsEmptyStringWhenPropertyIsFalse(): void
    {
        $post = new stdClass();
        $post->ID = false;

        $hooks = new HooksFacade();

        $resolver = new PostResolver($post, $hooks, '', $this->expirablePostModelFactory);

        $this->assertEquals('', $resolver->getValueAsString('ID'));
    }

    public function testIssetReturnsTrueWhenPropertyExists(): void
    {
        $post = new stdClass();
        $post->ID = 23;

        $hooks = new HooksFacade();

        $resolver = new PostResolver($post, $hooks, '', $this->expirablePostModelFactory);

        $this->assertTrue(isset($resolver->ID));
        $this->assertTrue(isset($resolver->id));
    }

    public function testIssetReturnsFalseWhenPropertyDoesNotExist(): void
    {
        $post = new stdClass();

        $hooks = new HooksFacade();

        $resolver = new PostResolver($post, $hooks, '', $this->expirablePostModelFactory);

        $this->assertFalse(isset($resolver->non_existent_key));
    }

    public function testSetDoesNothing(): void
    {
        $post = new stdClass();
        $post->ID = 23;

        $hooks = new HooksFacade();

        $resolver = new PostResolver($post, $hooks, '', $this->expirablePostModelFactory);

        $resolver->ID = 24;

        $this->assertEquals('23', $resolver->getValueAsString('ID'));
    }

    public function testUnsetDoesNothing(): void
    {
        $post = new stdClass();
        $post->ID = 23;

        $hooks = new HooksFacade();

        $resolver = new PostResolver($post, $hooks, '', $this->expirablePostModelFactory);

        unset($resolver->ID);

        $this->assertEquals('23', $resolver->getValueAsString('ID'));
    }

    public function test__toStringReturnsCorrectValue(): void
    {
        $post = new stdClass();
        $post->ID = 23;

        $hooks = new HooksFacade();

        $resolver = new PostResolver($post, $hooks, '', $this->expirablePostModelFactory);

        $this->assertEquals('23', (string)$resolver);
    }

    public function testCompactReturnsCompactedArray(): void
    {
        $post = new stdClass();
        $post->ID = 23;

        $hooks = new HooksFacade();

        $resolver = new PostResolver($post, $hooks, '', $this->expirablePostModelFactory);

        $this->assertEquals(['type' => 'post', 'value' => 23], $resolver->compact());
    }

    public function testGetContentReturnsCorrectValue(): void
    {
        $post = new stdClass();
        $post->ID = 23;
        $post->post_content = '<p>Test content</p>';

        $hooks = new HooksFacade();

        $resolver = new PostResolver($post, $hooks, '', $this->expirablePostModelFactory);

        $this->assertEquals('Test content', $resolver->getValue('post_content_text'));
        $this->assertEquals("<p>Test content</p>\n", $resolver->getValue('post_content'));
    }
}
