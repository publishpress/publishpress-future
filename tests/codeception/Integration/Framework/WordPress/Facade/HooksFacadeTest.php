<?php

namespace Tests\Framework\WordPress\Facade;

use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;

class HooksFacadeTest extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    public function testAddFilter(): void
    {
        $hooksFacade = new HooksFacade();

        $hooksFacade->addFilter('test_filter1', '__return_true');

        $this->assertEquals(10, has_filter('test_filter1'));
    }

    public function testRemoveFilter(): void
    {
        $hooksFacade = new HooksFacade();

        add_filter('test_filter2', '__return_true');

        $this->assertEquals(10, has_filter('test_filter2'));

        $hooksFacade->removeFilter('test_filter2', '__return_true');

        $this->assertEquals(0, has_filter('test_filter2'));
    }

    public function testApplyFilters(): void
    {
        $hooksFacade = new HooksFacade();

        add_filter('test_filter3', function ($value) {
            return $value . '!';
        });

        $this->assertEquals('test!', $hooksFacade->applyFilters('test_filter3', 'test'));
    }

    public function testAddAction(): void
    {
        $hooksFacade = new HooksFacade();
        $output = '';

        add_action('test_action4', function () use (&$output) {
            $output .= 'test!';
        });

        $hooksFacade->doAction('test_action4');

        $this->assertEquals('test!', $output);
    }

    public function testRemoveAction(): void
    {
        $hooksFacade = new HooksFacade();
        $count = 0;

        $callback = function () use (&$count) {
            $count++;
        };

        add_action('test_action5', $callback);

        do_action('test_action5');
        do_action('test_action5');

        $this->assertEquals(2, $count);

        $hooksFacade->removeAction('test_action5', $callback);

        do_action('test_action5');

        $this->assertEquals(2, $count);
    }

    public function testDoAction(): void
    {
        $hooksFacade = new HooksFacade();
        $output = '';

        add_action('test_action6', function () use (&$output) {
            $output .= 'test_action6';
        });

        $hooksFacade->doAction('test_action6');

        $this->assertEquals('test_action6', $output);
    }
}
