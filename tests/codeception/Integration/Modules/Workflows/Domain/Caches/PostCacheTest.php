<?php

namespace PublishPress\Future\Tests\Integration\Modules\Workflows\Domain\Caches;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Core\HookableInterface;
use PublishPress\Future\Modules\Workflows\Domain\Caches\PostCache;
use PublishPress\Future\Modules\Workflows\HooksAbstract;

class PostCacheTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    private $postCache;
    private $hookable;
    private $postId;
    private $postBefore;
    private $postAfter;

    public function setUp(): void
    {
        parent::setUp();

        // Create a real implementation of HookableInterface that uses WordPress hooks
        $container = Container::getInstance();
        $this->hookable = $container->get(ServicesAbstract::HOOKS);

        // Create the PostCache instance with real hookable implementation
        $this->postCache = new PostCache($this->hookable);

        // Setup the hooks
        $this->postCache->setup();
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    public function testCacheForFirstInsert(): void
    {
        $postId = wp_insert_post([
            'post_title' => 'Original Title',
            'post_content' => 'Original Content',
            'post_status' => 'publish',
        ]);

        $this->assertNotEmpty($postId);

        $cache = $this->postCache->getCacheForPostId($postId);
        $permalink = get_permalink($postId);

        $this->assertNotEmpty($cache);
        $this->assertArrayHasKey('postBefore', $cache);
        $this->assertArrayHasKey('postAfter', $cache);
        $this->assertArrayHasKey('permalinkBefore', $cache);
        $this->assertArrayHasKey('permalinkAfter', $cache);

        $this->assertNotNull($cache['postBefore']);
        $this->assertNotNull($cache['postAfter']);
        $this->assertNotNull($cache['permalinkBefore']);
        $this->assertNotNull($cache['permalinkAfter']);

        $this->assertEquals($cache['postBefore']->post_title, 'Original Title');
        $this->assertEquals($cache['postAfter']->post_title, 'Original Title');

        $this->assertEquals($cache['postBefore']->ID, $postId);
        $this->assertEquals($cache['postAfter']->ID, $postId);

        $this->assertEquals($cache['postBefore']->post_status, 'new');
        $this->assertEquals($cache['postAfter']->post_status, 'publish');

        $this->assertEquals($cache['permalinkBefore'], $permalink);
        $this->assertEquals($cache['permalinkAfter'], $permalink);
    }

    public function testCacheForUpdate(): void
    {
        $postId = wp_insert_post([
            'post_title' => 'Original Title',
            'post_content' => 'Original Content',
            'post_status' => 'draft',
        ]);

        $this->assertNotEmpty($postId);

        $postId = wp_update_post([
            'ID' => $postId,
            'post_title' => 'Updated Title',
            'post_content' => 'Updated Content',
        ]);

        $cache = $this->postCache->getCacheForPostId($postId);
        $permalink = get_permalink($postId);

        $this->assertNotEmpty($cache);
        $this->assertArrayHasKey('postBefore', $cache);
        $this->assertArrayHasKey('postAfter', $cache);
        $this->assertArrayHasKey('permalinkBefore', $cache);
        $this->assertArrayHasKey('permalinkAfter', $cache);

        $this->assertNotNull($cache['postBefore']);
        $this->assertNotNull($cache['postAfter']);
        $this->assertNotNull($cache['permalinkBefore']);
        $this->assertNotNull($cache['permalinkAfter']);

        $this->assertEquals($cache['postBefore']->post_title, 'Original Title');
        $this->assertEquals($cache['postAfter']->post_title, 'Updated Title');

        $this->assertEquals($cache['postBefore']->ID, $postId);
        $this->assertEquals($cache['postAfter']->ID, $postId);

        $this->assertEquals($cache['permalinkBefore'], $permalink);
        $this->assertEquals($cache['permalinkAfter'], $permalink);
    }

    public function testCacheForPostTransition(): void
    {
        $postId = wp_insert_post([
            'post_title' => 'Original Title',
            'post_content' => 'Original Content',
            'post_status' => 'publish',
        ]);

        $this->assertNotEmpty($postId);

        $postId = wp_update_post([
            'ID' => $postId,
            'post_status' => 'draft',
        ]);

        $cache = $this->postCache->getCacheForPostId($postId);

        $permalink = get_permalink($postId);

        $this->assertNotEmpty($cache);
        $this->assertArrayHasKey('postBefore', $cache);
        $this->assertArrayHasKey('postAfter', $cache);
        $this->assertArrayHasKey('permalinkBefore', $cache);
        $this->assertArrayHasKey('permalinkAfter', $cache);

        $this->assertNotNull($cache['postBefore']);
        $this->assertNotNull($cache['postAfter']);
        $this->assertNotNull($cache['permalinkBefore']);
        $this->assertNotNull($cache['permalinkAfter']);

        $this->assertEquals($cache['postBefore']->post_title, 'Original Title');
        $this->assertEquals($cache['postAfter']->post_title, 'Original Title');

        $this->assertEquals($cache['postBefore']->ID, $postId);
        $this->assertEquals($cache['postAfter']->ID, $postId);

        $this->assertEquals($cache['permalinkBefore'], $permalink);
        $this->assertEquals($cache['permalinkAfter'], $permalink);
    }

    public function testCacheForPostPublishedTransition(): void
    {
        $postId = wp_insert_post([
            'post_title' => 'Original Title',
            'post_content' => 'Original Content',
            'post_status' => 'draft',
        ]);

        $this->assertNotEmpty($postId);

        wp_publish_post($postId);

        $cache = $this->postCache->getCacheForPostId($postId);

        $permalink = get_permalink($postId);

        $this->assertNotEmpty($cache);
        $this->assertArrayHasKey('postBefore', $cache);
        $this->assertArrayHasKey('postAfter', $cache);
        $this->assertArrayHasKey('permalinkBefore', $cache);
        $this->assertArrayHasKey('permalinkAfter', $cache);

        $this->assertNotNull($cache['postBefore']);
        $this->assertNotNull($cache['postAfter']);
        $this->assertNotNull($cache['permalinkBefore']);
        $this->assertNotNull($cache['permalinkAfter']);

        $this->assertEquals($cache['postBefore']->post_title, 'Original Title');
        $this->assertEquals($cache['postAfter']->post_title, 'Original Title');

        $this->assertEquals($cache['postBefore']->ID, $postId);
        $this->assertEquals($cache['postAfter']->ID, $postId);

        $this->assertEquals($cache['permalinkBefore'], $permalink);
        $this->assertEquals($cache['permalinkAfter'], $permalink);
    }
}
