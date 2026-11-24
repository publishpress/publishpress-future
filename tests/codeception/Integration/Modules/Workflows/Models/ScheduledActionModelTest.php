<?php

namespace Tests\Modules\Workflows\Models;

use Exception;
use PublishPress\Future\Modules\Workflows\Models\ScheduledActionModel;
use PublishPress\Future\Modules\Workflows\Models\WorkflowScheduledStepModel;

class ScheduledActionModelTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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

    public function testLoadByActionIdWithShortArgs()
    {
        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = 'test_hook'");

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}actionscheduler_actions (action_id, hook, status, priority, args) VALUES (%d, %s, %s, %d, %s)",
                [1, 'test_hook', 'pending', 10, '[{"test":"test"}]']
            )
        );

        $model = new ScheduledActionModel();
        $model->loadByActionId(1);

        $this->assertEquals(1, $model->getActionId());
        $this->assertEquals('test_hook', $model->getHook());
        $this->assertEquals('pending', $model->getStatus());
        $this->assertEquals(10, $model->getPriority());
        $this->assertEquals([['test' => 'test']], $model->getArgs());
    }

    public function testLoadByActionIdWithExtendedArgs()
    {
        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = 'test_hook'");

        $args = [
            [
                'key1' => 'value1',
                'key2' => 'abcdefghijklmnopqrstuvwxyz1234567890',
                'key3' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890',
                'key4' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
                'key5' => 'Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam.',
            ]
        ];

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}actionscheduler_actions (action_id, hook, status, priority, args, extended_args) VALUES (%d, %s, %s, %d, %s, %s)",
                [1, 'test_hook', 'pending', 10, md5(json_encode($args)), json_encode($args)]
            )
        );

        $model = new ScheduledActionModel();
        $model->loadByActionId(1);

        $this->assertEquals(1, $model->getActionId());
        $this->assertEquals('test_hook', $model->getHook());
        $this->assertEquals('pending', $model->getStatus());
        $this->assertEquals(10, $model->getPriority());
        $this->assertEquals($args, $model->getArgs());
    }

    public function testLoadByActionIdThrowsExceptionIfActionNotFound()
    {
        $model = new ScheduledActionModel();
        $this->expectException(Exception::class);
        $model->loadByActionId(1);
    }

    public function testSetArgs()
    {
        $model = new ScheduledActionModel();
        $model->setArgs([['test' => 'test']]);
        $this->assertEquals([['test' => 'test']], $model->getArgs());
    }

    public function testUpdate()
    {
        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = 'test_hook'");

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}actionscheduler_actions (action_id, hook, status, priority, args) VALUES (%d, %s, %s, %d, %s)",
                [1, 'test_hook', 'pending', 10, '[{"test":"test"}]']
            )
        );

        $model = new ScheduledActionModel();
        $model->loadByActionId(1);
        $model->setArgs([['test' => 'test2']]);
        $model->update();

        $this->assertEquals(1, $model->getActionId());
        $this->assertEquals('test_hook', $model->getHook());
        $this->assertEquals('pending', $model->getStatus());
        $this->assertEquals(10, $model->getPriority());
        $this->assertEquals([['test' => 'test2']], $model->getArgs());
    }

    public function testSetActionIdOnArgs()
    {
        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = 'test_hook'");

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}actionscheduler_actions (action_id, hook, status, priority, args) VALUES (%d, %s, %s, %d, %s)",
                [1, 'test_hook', 'pending', 10, '[{"test":"test"}]']
            )
        );

        $model = new ScheduledActionModel();
        $model->loadByActionId(1);
        $model->setActionIdOnArgs();
        $this->assertEquals([['test' => 'test', 'actionId' => 1]], $model->getArgs());
    }

    public function testArgsAreOnNewFormatReturnsFalseIfArgsAreNotOnNewFormat()
    {
        $args = [
            [
                'key1' => 'value1',
            ]
        ];

        $this->assertFalse(ScheduledActionModel::argsAreOnNewFormat($args));

        $args = [
            'pluginVersion' => '3.4.4',
            'args' => [
                'key1' => 'value1',
            ]
        ];

        $this->assertFalse(ScheduledActionModel::argsAreOnNewFormat($args));
    }

    public function testArgsAreOnNewFormatReturnsTrueIfArgsAreOnNewFormat()
    {
        $args = [
            'pluginVersion' => '4.0.0-alpha.1',
            'args' => [
                'key1' => 'value1',
            ]
        ];

        $this->assertTrue(ScheduledActionModel::argsAreOnNewFormat($args));

        $args = [
            'pluginVersion' => '4.0.1',
            'args' => [
                'key1' => 'value1',
            ]
        ];

        $this->assertTrue(ScheduledActionModel::argsAreOnNewFormat($args));
    }

    public function testCancel()
    {
        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = 'test_hook'");

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}actionscheduler_actions (action_id, hook, status, priority, args) VALUES (%d, %s, %s, %d, %s)",
                [1, 'test_hook', 'pending', 10, '[{"test":"test"}]']
            )
        );

        $model = new ScheduledActionModel();
        $model->loadByActionId(1);
        $model->cancel();

        // Test the status is updated without reloading the model
        $this->assertEquals('canceled', $model->getStatus());

        // Test the status is updated reloading the model
        $model->loadByActionId(1);
        $this->assertEquals('canceled', $model->getStatus());
    }

    public function testGetActionId()
    {
        $model = $this->make(ScheduledActionModel::class, [
            'actionId' => 1,
        ]);
        $this->assertEquals(1, $model->getActionId());
    }

    public function testGetHook()
    {
        $model = $this->make(ScheduledActionModel::class, [
            'hook' => 'test_hook',
        ]);
        $this->assertEquals('test_hook', $model->getHook());
    }

    public function testGetStatus()
    {
        $model = $this->make(ScheduledActionModel::class, [
            'status' => 'pending',
        ]);
        $this->assertEquals('pending', $model->getStatus());
    }

    public function testGetPriority()
    {
        $model = $this->make(ScheduledActionModel::class, [
            'priority' => 10,
        ]);
        $this->assertEquals(10, $model->getPriority());
    }

    public function testGetArgs()
    {
        $model = $this->make(ScheduledActionModel::class, [
            'args' => [
                'key1' => 'value1',
            ],
        ]);
        $this->assertEquals(['key1' => 'value1'], $model->getArgs());
    }

    public function testComplete()
    {
        global $wpdb;

        $wpdb->query("DELETE FROM {$wpdb->prefix}actionscheduler_actions WHERE hook = 'test_hook'");

        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$wpdb->prefix}actionscheduler_actions (action_id, hook, status, priority, args) VALUES (%d, %s, %s, %d, %s)",
                [1, 'test_hook', 'pending', 10, '[{"test":"test"}]']
            )
        );

        $model = new ScheduledActionModel();
        $model->loadByActionId(1);

        $this->assertEquals('pending', $model->getStatus());

        $model->complete();
        $model->loadByActionId(1);
        $this->assertEquals('completed', $model->getStatus());
    }
}
