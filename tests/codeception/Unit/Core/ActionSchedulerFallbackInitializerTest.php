<?php

/**
 * Copyright (c) 2026, PublishPress
 */

namespace unit\Core;

use Codeception\Test\Unit;
use PublishPress\Future\Core\ActionSchedulerFallbackInitializer;
use UnitTester;

class ActionSchedulerFallbackInitializerTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    public function testResolveBootstrapFileReturnsVendoredActionSchedulerBootstrap()
    {
        $root = dirname(__DIR__, 4);
        $classFile = $root . '/lib/vendor/woocommerce/action-scheduler/classes/abstracts/ActionScheduler.php';
        $expected = $root . '/lib/vendor/woocommerce/action-scheduler/action-scheduler.php';

        $result = ActionSchedulerFallbackInitializer::resolveBootstrapFile($classFile);

        $this->assertSame(realpath($expected), realpath($result));
    }

    public function testResolveBootstrapFileReturnsNullForUnrelatedPath()
    {
        $result = ActionSchedulerFallbackInitializer::resolveBootstrapFile(
            '/not/a/real/publishpress-future/path/ActionScheduler.php'
        );

        $this->assertNull($result);
    }

    public function testEnsureInitializedDoesNothingWhenProceduralApiIsLoaded()
    {
        // action-scheduler.php only defines as_* when add_action() exists.
        // The unit bootstrap has no WordPress hook API, so load the procedural
        // file directly when the symbol is not already declared.
        if (! function_exists('as_get_scheduled_actions')) {
            require_once dirname(__DIR__, 4) . '/lib/vendor/woocommerce/action-scheduler/functions.php';
        }

        $this->assertTrue(function_exists('as_get_scheduled_actions'));

        ActionSchedulerFallbackInitializer::ensureInitialized();

        $this->assertTrue(function_exists('as_get_scheduled_actions'));
    }
}
