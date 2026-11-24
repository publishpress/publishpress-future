<?php

namespace Tests\Modules\Workflows\Models;

use Exception;
use PublishPress\Future\Modules\Workflows\Models\WorkflowScheduledStepModel;

class WorkflowScheduledStepModelTest extends \lucatume\WPBrowser\TestCase\WPTestCase
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

    public function testSetAndGetActionId(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setActionId(1);
        $this->assertEquals(1, $model->getActionId());
    }

    public function testSetAndGetWorkflowId(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setWorkflowId(1);
        $this->assertEquals(1, $model->getWorkflowId());
    }

    public function testSetAndGetStepId(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setStepId('step_id');
        $this->assertEquals('step_id', $model->getStepId());
    }

    public function testSetAndGetActionUID(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setActionUID('action_uid');
        $this->assertEquals('action_uid', $model->getActionUID());
    }

    public function testSetAndGetActionUIDHash(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setActionUID('action_uid');
        $this->assertEquals(md5('action_uid'), $model->getActionUIDHash());
    }

    public function testSetAndGetIsRecurring(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setIsRecurring(true);
        $this->assertTrue($model->getIsRecurring());
    }

    public function testSetAndGetRepeatUntil(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setRepeatUntil('times');
        $this->assertEquals('times', $model->getRepeatUntil());
    }

    public function testSetAndGetRepeatTimes(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setRepeatTimes(1);
        $this->assertEquals(1, $model->getRepeatTimes());
    }

    public function testSetAndGetRepeatUntilDate(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setRepeatUntilDate('2024-01-01 00:00:00');
        $this->assertEquals('2024-01-01 00:00:00', $model->getRepeatUntilDate());
    }

    public function testSetAndgetTotalRunCount(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setActionId(1);
        $model->setWorkflowId(1);
        $model->setStepId('step_id');
        $model->setActionUID('action_uid');
        $model->setTotalRunCount(1);
        $model->setArgs(['key' => 'value']);
        $model->insert();
        $this->assertEquals(1, $model->getTotalRunCount());

        $model->loadByActionId(1);
        $this->assertEquals(1, $model->getTotalRunCount());

        $model->setTotalRunCount(2);
        $model->update();

        $model->loadByActionId(1);
        $this->assertEquals(2, $model->getTotalRunCount());
    }

    public function testSetAndGetLastRunAt(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setActionId(1);
        $model->setWorkflowId(1);
        $model->setStepId('step_id');
        $model->setActionUID('action_uid');
        $model->setLastRunAt('2024-01-01 00:00:00');
        $model->setArgs(['key' => 'value']);
        $model->insert();

        $model->loadByActionId(1);
        $this->assertEquals('2024-01-01 00:00:00', $model->getLastRunAt());
    }

    public function testSetAndGetArgs(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setArgs(['key' => 'value']);
        $this->assertEquals(['key' => 'value'], $model->getArgs());
    }

    public function testInsertForUncompressedArgs(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class, [
            'isCompressed' => false,
        ]);
        $model->setActionId(2);
        $model->setWorkflowId(1);
        $model->setStepId('step_id');
        $model->setActionUID('action_uid');
        $model->setArgs(['key' => 'value']);
        $result = $model->insert();

        $this->assertTrue($result);

        global $wpdb;

        $tableName = $wpdb->prefix . 'ppfuture_workflow_scheduled_steps';

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM %i WHERE action_id = %d",
                $tableName,
                $model->getActionId()
            ),
            ARRAY_A
        );

        $this->assertNotEmpty($result);
        $this->assertEquals(1, $result['workflow_id']);
        $this->assertEquals('step_id', $result['step_id']);
        $this->assertEquals('action_uid', $result['action_uid']);
        $this->assertEquals(md5('action_uid'), $result['action_uid_hash']);
        $this->assertEquals(0, $result['is_compressed']);
        $this->assertNull($result['compressed_args']);
        $this->assertNotEmpty($result['uncompressed_args']);

        // Verify the uncompressed args
        $this->assertEquals('{"key":"value"}', $result['uncompressed_args']);
    }

    public function testUpdate(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class, [
            'isCompressed' => false,
        ]);
        $model->setActionId(3);
        $model->setWorkflowId(1);
        $model->setStepId('step_id');
        $model->setActionUID('action_uid');
        $model->setArgs(['key' => 'value']);
        $model->setLastRunAt('2024-01-01 00:00:00');
        $model->setTotalRunCount(1);
        $model->setRepeatTimes(1);
        $model->setRepeatUntilDate('2024-01-01 00:00:00');
        $model->setIsRecurring(true);
        $model->setRepeatUntil('times');
        $model->insert();

        $model->setWorkflowId(2);
        $model->setStepId('new_step_id');
        $model->setActionUID('new_action_uid');
        $model->setArgs(['key' => 'new_value']);
        $model->setLastRunAt('2024-01-03 00:00:00');
        $model->setTotalRunCount(2);
        $model->setRepeatTimes(2);
        $model->setRepeatUntilDate('2024-01-05 00:00:00');
        $model->setIsRecurring(false);
        $model->setRepeatUntil('date');
        $result = $model->update();

        $this->assertTrue($result);

        global $wpdb;

        $tableName = $wpdb->prefix . 'ppfuture_workflow_scheduled_steps';

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM %i WHERE action_id = %d",
                $tableName,
                $model->getActionId()
            ),
            ARRAY_A
        );

        $this->assertNotEmpty($result);
        $this->assertEquals(2, $result['workflow_id']);
        $this->assertEquals('new_step_id', $result['step_id']);
        $this->assertEquals('new_action_uid', $result['action_uid']);
        $this->assertEquals(md5('new_action_uid'), $result['action_uid_hash']);
        $this->assertEquals(0, $result['is_compressed']);
        $this->assertNull($result['compressed_args']);
        $this->assertNotEmpty($result['uncompressed_args']);
        $this->assertEquals('{"key":"new_value"}', $result['uncompressed_args']);
        $this->assertEquals(2, $result['repeat_times']);
        $this->assertEquals('2024-01-05 00:00:00', $result['repeat_until_date']);
        $this->assertEquals(false, $result['is_recurring']);
        $this->assertEquals('date', $result['repeat_until']);
    }

    public function testDelete(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class, [
            'isCompressed' => false,
        ]);
        $model->setActionId(4);
        $model->setWorkflowId(1);
        $model->setStepId('step_id');
        $model->setActionUID('action_uid');
        $model->setArgs(['key' => 'value']);
        $model->insert();

        $result = $model->delete();

        $this->assertTrue($result);

        global $wpdb;

        $tableName = $wpdb->prefix . 'ppfuture_workflow_scheduled_steps';

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM %i WHERE action_id = %d",
                $tableName,
                $model->getActionId()
            ),
            ARRAY_A
        );

        $this->assertNull($result);
    }

    public function testLoadByActionIdForUncompressedArgs(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class, [
            'isCompressed' => false,
        ]);
        $model->setActionId(5);
        $model->setWorkflowId(1);
        $model->setStepId('step_id');
        $model->setActionUID('action_uid');
        $model->setArgs(['key' => 'value']);
        $model->insert();

        $loadedModel = $this->make(WorkflowScheduledStepModel::class, [
            'isCompressed' => false,
        ]);
        $loadedModel->loadByActionId($model->getActionId());

        $this->assertEquals($model->getActionId(), $loadedModel->getActionId());
        $this->assertEquals($model->getWorkflowId(), $loadedModel->getWorkflowId());
        $this->assertEquals($model->getStepId(), $loadedModel->getStepId());
        $this->assertEquals($model->getActionUID(), $loadedModel->getActionUID());
        $this->assertEquals($model->getArgs(), $loadedModel->getArgs());
    }

    public function testLoadByActionIdThrowsExceptionWhenNotFound(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $this->expectException(Exception::class);
        $model->loadByActionId(9999);
    }

    public function testincrementTotalRunCount(): void
    {
        global $wpdb;

        $query = $wpdb->prepare(
            "INSERT INTO {$wpdb->prefix}ppfuture_workflow_scheduled_steps (action_id, workflow_id, step_id, action_uid_hash, action_uid, is_recurring, repeat_until, repeat_times, repeat_until_date, is_compressed, compressed_args, uncompressed_args, created_at) VALUES (%d, %d, %s, %s, %s, %d, %s, %d, %s, %d, %s, %s, %s)",
            1,
            1,
            'step_id',
            md5('action_uid'),
            'action_uid',
            1,
            'forever',
            0,
            '2024-01-01 00:00:00',
            0,
            null,
            null,
            '2024-01-01 00:00:00'
        );

        $wpdb->query($query);

        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->loadByActionId(1);

        $this->assertEquals(0, $model->getTotalRunCount());

        $model->incrementTotalRunCount();
        $model->update();

        $model->incrementTotalRunCount();
        $model->update();

        $model->loadByActionId(1);
        $this->assertEquals(2, $model->getTotalRunCount());
    }

    public function testUpdateLastRunAt(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setActionId(7);
        $model->setWorkflowId(1);
        $model->setStepId('step_id');
        $model->setActionUID('action_uid');
        $model->setArgs(['key' => 'value']);
        $model->setLastRunAt('2024-01-01 00:00:00');

        $model->insert();

        $model->updateLastRunAt();
        $model->update();

        $model->loadByActionId(7);
        $this->assertNotEmpty($model->getLastRunAt());
        $this->assertEquals(current_time('mysql'), $model->getLastRunAt());
    }

    public function testResetRunData(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setActionId(8);
        $model->setWorkflowId(1);
        $model->setStepId('step_id');
        $model->setActionUID('action_uid');
        $model->setArgs(['key' => 'value']);
        $model->setTotalRunCount(2);
        $model->setLastRunAt('2024-01-01 00:00:00');
        $model->insert();

        $model->resetRunData();
        $model->update();

        $model->loadByActionId(8);
        $this->assertEquals(0, $model->getTotalRunCount());
        $this->assertEquals('0000-00-00 00:00:00', $model->getLastRunAt());
    }

    public function testMarkAsFinished(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setActionId(9);
        $model->setWorkflowId(1);
        $model->setStepId('step_id');
        $model->setActionUID('action_uid');
        $model->setArgs(['key' => 'value']);
        $model->insert();

        $postMeta = get_post_meta($model->getWorkflowId(), WorkflowScheduledStepModel::META_FINISHED_PREFIX . $model->getActionUIDHash(), true);
        $this->assertEmpty($postMeta);

        $model->markAsFinished();

        $postMeta = get_post_meta($model->getWorkflowId(), WorkflowScheduledStepModel::META_FINISHED_PREFIX . $model->getActionUIDHash(), true);
        $this->assertEquals(1, $postMeta);
    }

    public function testIsFinished(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setActionId(10);
        $model->setWorkflowId(1);
        $model->setStepId('step_id');
        $model->setActionUID('action_uid');
        $model->setArgs(['key' => 'value']);
        $model->insert();

        $this->assertFalse($model->isFinished());

        add_post_meta(1, WorkflowScheduledStepModel::META_FINISHED_PREFIX . $model->getActionUIDHash(), true);

        $model->loadByActionId(10);
        $this->assertTrue($model->isFinished());
    }

    public function testGetMetaFinished(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setActionId(11);
        $model->setWorkflowId(1);
        $model->setStepId('step_id');
        $model->setActionUID('action_uid');
        $model->setArgs(['key' => 'value']);
        $model->insert();

        $this->assertEquals(0, WorkflowScheduledStepModel::getMetaIsFinished(1, $model->getActionUIDHash()));

        add_post_meta(1, WorkflowScheduledStepModel::META_FINISHED_PREFIX . $model->getActionUIDHash(), true);

        $this->assertEquals(1, WorkflowScheduledStepModel::getMetaIsFinished(1, $model->getActionUIDHash()));
    }

    public function testGetMetaRunCount(): void
    {
        $model = $this->make(WorkflowScheduledStepModel::class);
        $model->setActionId(12);
        $model->setWorkflowId(1);
        $model->setStepId('step_id');
        $model->setActionUID('action_uid');
        $model->setArgs(['key' => 'value']);
        $model->insert();

        $this->assertEquals(0, WorkflowScheduledStepModel::getMetaRunCount(1, $model->getActionUIDHash()));

        $model->incrementTotalRunCount();
        $model->update();

        $this->assertEquals(1, WorkflowScheduledStepModel::getMetaRunCount(1, $model->getActionUIDHash()));
    }
}
