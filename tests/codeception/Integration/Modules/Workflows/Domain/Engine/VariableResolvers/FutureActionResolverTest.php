<?php

namespace Tests\Modules\Workflows\Domain\Engine\VariableResolvers;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Expirator\ExpirationActionsAbstract;
use PublishPress\Future\Modules\Workflows\Domain\Engine\VariableResolvers\FutureActionResolver;
use PublishPress\Future\Modules\Expirator\Models\ExpirablePostModel;
use stdClass;

class FutureActionResolverTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var \Closure
     */
    private $postModelFactory;

    public function setUp(): void
    {
        parent::setUp();

        // Mock ExpirablePostModel using anonymous class
        $this->postModelFactory = Container::getInstance()->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY);
    }

    public function testGetTypeReturnsCorrectType(): void
    {
        $post = new stdClass();
        $post->ID = 1;

        $resolver = new FutureActionResolver($post, $this->postModelFactory);

        $this->assertEquals('future_action', $resolver->getType());
    }

    public function testGetValueReturnsCorrectValues(): void
    {
        $postId = $this->factory()->post->create();
        $post = get_post($postId);

        $scheduler = Container::getInstance()->get(ServicesAbstract::EXPIRATION_SCHEDULER);
        $scheduler->schedule($postId, 1742239607, [
            'expireType' => ExpirationActionsAbstract::POST_STATUS_TO_DRAFT,
            'new_status' => 'draft',
        ]);

        $resolver = new FutureActionResolver($post, $this->postModelFactory);

        $this->assertTrue($resolver->getValue('enabled'));
        $this->assertEquals(ExpirationActionsAbstract::CHANGE_POST_STATUS, $resolver->getValue('action'));
        $this->assertEquals(1742239607, $resolver->getValue('date'));
        $this->assertEquals('2025-03-17 19:26:47', $resolver->getValue('date_string'));
        $this->assertEquals('draft', $resolver->getValue('new_status'));
    }

    public function testGetValueAsStringReturnsCorrectValues(): void
    {
        $postId = $this->factory()->post->create();
        $post = get_post($postId);

        $scheduler = Container::getInstance()->get(ServicesAbstract::EXPIRATION_SCHEDULER);
        $scheduler->schedule($postId, 1742239607, [
            'expireType' => ExpirationActionsAbstract::POST_STATUS_TO_DRAFT,
            'new_status' => 'draft',
        ]);


        $resolver = new FutureActionResolver($post, $this->postModelFactory);

        $this->assertEquals('1', $resolver->getValueAsString('enabled'));
        $this->assertEquals('1742239607', $resolver->getValueAsString('date'));
        $this->assertEquals('2025-03-17 19:26:47', $resolver->getValueAsString('date_string'));
    }

    public function testCompactReturnsCorrectArray(): void
    {
        $postId = $this->factory()->post->create();
        $post = get_post($postId);

        $scheduler = Container::getInstance()->get(ServicesAbstract::EXPIRATION_SCHEDULER);
        $scheduler->schedule($postId, 1742239607, [
            'expireType' => ExpirationActionsAbstract::POST_STATUS_TO_DRAFT,
            'new_status' => 'draft',
        ]);

        $resolver = new FutureActionResolver($post, $this->postModelFactory);

        $this->assertEquals(['type' => 'future_action'], $resolver->compact());
    }

    public function testIssetReturnsCorrectValues(): void
    {
        $post = new stdClass();
        $post->ID = 1;

        $resolver = new FutureActionResolver($post, $this->postModelFactory);

        $this->assertTrue(isset($resolver->enabled));
        $this->assertTrue(isset($resolver->action));
        $this->assertTrue(isset($resolver->date));
        $this->assertTrue(isset($resolver->date_string));
        $this->assertTrue(isset($resolver->terms));
        $this->assertTrue(isset($resolver->new_status));
        $this->assertFalse(isset($resolver->non_existent_property));
    }

    public function testGetTermsReturnsCorrectStructure(): void
    {
        $postId = $this->factory()->post->create();
        $post = get_post($postId);

        // Create test terms
        $term1 = $this->factory()->term->create([
            'name' => 'Term 1',
            'taxonomy' => 'category',
        ]);
        $term2 = $this->factory()->term->create([
            'name' => 'Term 2',
            'taxonomy' => 'category',
        ]);
        $term3 = $this->factory()->term->create([
            'name' => 'Term 3',
            'taxonomy' => 'category',
        ]);

        $scheduler = Container::getInstance()->get(ServicesAbstract::EXPIRATION_SCHEDULER);
        $scheduler->schedule($postId, 1742239607, [
            'expireType' => ExpirationActionsAbstract::POST_CATEGORY_ADD,
            'category' => [$term1, $term2, $term3],
        ]);

        $resolver = new FutureActionResolver($post, $this->postModelFactory);
        $terms = $resolver->getValue('terms');

        $this->assertIsObject($terms);

        $terms = $terms->getValue();
        $this->assertEquals([$term1, $term2, $term3], $terms['ids']);
        $this->assertEquals(['Term 1', 'Term 2', 'Term 3'], $terms['labels']);
    }
}
