<?php

namespace Tests\Modules\Workflows\Controllers;

use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Expirator\HooksAbstract as ExpiratorHooksAbstract;
use PublishPress\Future\Modules\Workflows\Controllers\PostsList;

class PostsListTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var PostsList|null
     */
    private $postsList;

    /**
     * @var list<int>
     */
    private $postIdsToDelete = [];

    public function tearDown(): void
    {
        foreach ($this->postIdsToDelete as $postId) {
            wp_delete_post($postId, true);
        }
        $this->postIdsToDelete = [];

        if ($this->postsList instanceof PostsList) {
            remove_action(
                ExpiratorHooksAbstract::ACTION_MANAGE_POSTS_CUSTOM_COLUMN,
                [$this->postsList, 'managePostsCustomColumn'],
                10
            );
            remove_action(
                ExpiratorHooksAbstract::ACTION_MANAGE_PAGES_CUSTOM_COLUMN,
                [$this->postsList, 'managePostsCustomColumn'],
                10
            );
            remove_filter(
                ExpiratorHooksAbstract::FILTER_MANAGE_POSTS_COLUMNS,
                [$this->postsList, 'addPostColumns'],
                10
            );
            remove_filter(
                ExpiratorHooksAbstract::FILTER_MANAGE_PAGES_COLUMNS,
                [$this->postsList, 'addPageColumns']
            );
        }

        parent::tearDown();
    }

    public function testManagePostsCustomColumnWithPostIdWhenGlobalPostIsNull(): void
    {
        $postId = (int) wp_insert_post([
            'post_title' => 'Test post for Future Action column',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'post',
        ]);
        $this->postIdsToDelete[] = $postId;

        $GLOBALS['post'] = null;

        $postsList = new PostsList(new HooksFacade());

        ob_start();
        $postsList->managePostsCustomColumn('expirationdate', $postId);
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function testManagePostsCustomColumnWithNullPostIdAndNoGlobalPost(): void
    {
        unset($GLOBALS['post']);

        $postsList = new PostsList(new HooksFacade());

        ob_start();
        $postsList->managePostsCustomColumn('expirationdate', null);
        $output = ob_get_clean();

        $this->assertSame('', $output);
    }

    public function testInitializeRegistersCustomColumnHooksWithTwoAcceptedArgs(): void
    {
        $this->postsList = new PostsList(new HooksFacade());
        $this->postsList->initialize();

        global $wp_filter;

        $hookNames = [
            ExpiratorHooksAbstract::ACTION_MANAGE_POSTS_CUSTOM_COLUMN,
            ExpiratorHooksAbstract::ACTION_MANAGE_PAGES_CUSTOM_COLUMN,
        ];

        foreach ($hookNames as $hookName) {
            $this->assertArrayHasKey($hookName, $wp_filter);

            $wpHook = $wp_filter[$hookName];
            $this->assertArrayHasKey(10, $wpHook->callbacks);

            $found = false;
            foreach ($wpHook->callbacks[10] as $callback) {
                if (
                    is_array($callback['function'])
                    && $callback['function'][0] === $this->postsList
                    && $callback['function'][1] === 'managePostsCustomColumn'
                ) {
                    $found = true;
                    $this->assertSame(2, $callback['accepted_args']);
                    break;
                }
            }

            $this->assertTrue(
                $found,
                sprintf('PostsList managePostsCustomColumn hook not found at priority 10 for %s.', $hookName)
            );
        }
    }
}
