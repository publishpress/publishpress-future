<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\PostMetaResolver;
use stdClass;

class PostMetaResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var int
     */
    private $postId;

    public function setUp(): void
    {
        parent::setUp();

        // Create a test post
        $this->postId = $this->factory()->post->create([
            'post_title' => 'Test Post',
            'post_content' => 'Test content',
            'post_status' => 'publish'
        ]);

        // Add some test meta
        add_post_meta($this->postId, 'test_meta_key', 'test_meta_value');
        add_post_meta($this->postId, 'test_multi_meta', ['value1', 'value2']);
        add_post_meta($this->postId, 'test_number', 42);
    }

    public function tearDown(): void
    {
        wp_delete_post($this->postId, true);
        parent::tearDown();
    }

    public function testGetTypeReturnsCorrectType(): void
    {
        $resolver = new PostMetaResolver($this->postId);

        $this->assertEquals('post_meta', $resolver->getType());
    }

    public function testGetValueReturnsCorrectMetaValue(): void
    {
        $resolver = new PostMetaResolver($this->postId);

        $this->assertEquals('test_meta_value', $resolver->getValue('test_meta_key'));
    }

    public function testGetValueReturnsFirstValueForMultipleMeta(): void
    {
        $resolver = new PostMetaResolver($this->postId);

        $this->assertEquals(['value1', 'value2'], $resolver->getValue('test_multi_meta'));
    }

    public function testGetValueReturnsEmptyStringForNonExistentMeta(): void
    {
        $resolver = new PostMetaResolver($this->postId);

        $this->assertEquals('', $resolver->getValue('non_existent_key'));
    }

    public function testGetValueAsStringReturnsStringValue(): void
    {
        $resolver = new PostMetaResolver($this->postId);

        $this->assertEquals('42', $resolver->getValueAsString('test_number'));
    }

    public function testGetValueAsStringReturnsEmptyStringForNonExistentMeta(): void
    {
        $resolver = new PostMetaResolver($this->postId);

        $this->assertEquals('', $resolver->getValueAsString('non_existent_key'));
    }

    public function testCompactReturnsCorrectArray(): void
    {
        $resolver = new PostMetaResolver($this->postId);

        $this->assertEquals(
            ['type' => 'post_meta', 'value' => $this->postId],
            $resolver->compact()
        );
    }

    public function testIssetReturnsTrueForExistingMeta(): void
    {
        $resolver = new PostMetaResolver($this->postId);

        $this->assertTrue(isset($resolver->test_meta_key));
    }

    public function testIssetReturnsFalseForNonExistentMeta(): void
    {
        $resolver = new PostMetaResolver($this->postId);

        $this->assertFalse(isset($resolver->non_existent_key));
    }

    public function testGetVariableReturnsAllMetaData(): void
    {
        $resolver = new PostMetaResolver($this->postId);

        $variable = $resolver->getVariable();

        $this->assertEquals($this->postId, $variable);
    }

    public function testSetDoesNotModifyMetaValue(): void
    {
        $resolver = new PostMetaResolver($this->postId);

        $resolver->test_meta_key = 'new_value';

        $this->assertEquals('test_meta_value', $resolver->getValue('test_meta_key'));
    }

    public function testHandlesSerializedMetaData(): void
    {
        $serializedData = serialize(['key' => 'value']);
        add_post_meta($this->postId, 'serialized_meta', $serializedData);

        $resolver = new PostMetaResolver($this->postId);

        $this->assertEquals($serializedData, $resolver->getValue('serialized_meta'));
    }

    public function testHandlesNumericMetaValues(): void
    {
        add_post_meta($this->postId, 'integer_meta', 123);
        add_post_meta($this->postId, 'float_meta', 123.45);

        $resolver = new PostMetaResolver($this->postId);

        $this->assertEquals('123', $resolver->getValueAsString('integer_meta'));
        $this->assertEquals('123.45', $resolver->getValueAsString('float_meta'));
    }
}
