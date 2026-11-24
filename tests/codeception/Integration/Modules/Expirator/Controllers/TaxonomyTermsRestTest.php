<?php

/**
 * Terms route returns taxonomies only for supported post types.
 *
 * @package PublishPress\Future
 * @author PublishPress
 * @copyright Copyright (c) 2026, PublishPress
 * @license GPLv2 or later
 */

namespace Tests\Modules\Expirator\Controllers;

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract;
use PublishPress\Future\Modules\Expirator\CapabilitiesAbstract;
use PublishPress\Future\Modules\Expirator\Controllers\RestAPIController;
use WP_REST_Request;
use WP_REST_Response;
use WP_User;

class TaxonomyTermsRestTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @since 4.12.0
     *
     * @return RestAPIController
     */
    private function controller(): RestAPIController
    {
        $container = Container::getInstance();

        return new RestAPIController(
            $container->get(ServicesAbstract::HOOKS),
            $container->get(ServicesAbstract::EXPIRABLE_POST_MODEL_FACTORY),
            $container->get(ServicesAbstract::CURRENT_USER_MODEL_FACTORY),
            $container->get(ServicesAbstract::LOGGER),
            $container->get(ServicesAbstract::DATE_TIME_HANDLER),
            $container->get(ServicesAbstract::TAXONOMIES_MODEL_FACTORY)
        );
    }

    /**
     * @since 4.12.0
     *
     * @return void
     */
    private function setCurrentUserWhoCanExpirePosts(): void
    {
        $userId = (int) $this->factory()->user->create(
            [
                'role' => 'author',
            ]
        );

        $user = new WP_User($userId);
        $user->add_cap(CapabilitiesAbstract::EXPIRE_POST);
        wp_set_current_user($userId);
    }

    /**
     * @since 4.12.0
     *
     * @param string $taxonomy
     * @return array<string, mixed>
     */
    private function getTermsData(string $taxonomy): array
    {
        $request = new WP_REST_Request('GET', '/publishpress-future/v1/terms/' . $taxonomy);
        $request->set_param('taxonomy', $taxonomy);

        $response = $this->controller()->getTaxonomyTerms($request);

        $this->assertInstanceOf(WP_REST_Response::class, $response);

        $data = $response->get_data();
        $this->assertIsArray($data);

        return $data;
    }

    /**
     * Category and post_tag stay available because they belong to post.
     *
     * @since 4.12.0
     */
    public function test_should_return_terms_when_taxonomy_belongs_to_supported_post_type(): void
    {
        $this->setCurrentUserWhoCanExpirePosts();

        $category = $this->getTermsData('category');
        $categoryObject = get_taxonomy('category');

        $this->assertIsObject($categoryObject);
        $this->assertIsArray($category['terms']);
        $this->assertSame(count($category['terms']), $category['count']);
        $this->assertSame($categoryObject->labels->name, $category['taxonomyName']);
        $this->assertNotSame('', $category['taxonomyName']);

        $postTag = $this->getTermsData('post_tag');
        $postTagObject = get_taxonomy('post_tag');

        $this->assertIsObject($postTagObject);
        $this->assertIsArray($postTag['terms']);
        $this->assertSame(count($postTag['terms']), $postTag['count']);
        $this->assertSame($postTagObject->labels->name, $postTag['taxonomyName']);
        $this->assertNotSame('', $postTag['taxonomyName']);
    }

    /**
     * nav_menu is not registered on a public or show_ui post type.
     *
     * @since 4.12.0
     */
    public function test_should_return_empty_terms_when_taxonomy_is_not_on_supported_post_type(): void
    {
        $this->setCurrentUserWhoCanExpirePosts();

        $data = $this->getTermsData('nav_menu');

        $this->assertSame([], $data['terms']);
        $this->assertSame(0, $data['count']);
        $this->assertSame('', $data['taxonomyName']);
    }
}
