<?php

namespace Tests\Modules\Workflows\DBTableSchemas;

use Codeception\Stub;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaHandlerInterface;
use PublishPress\Future\Framework\Database\DBTableSchemaHandler;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Modules\Workflows\DBTableSchemas\WorkflowScheduledStepsSchema;
use Tests\NoTransactionWPTestCase;

class WorkflowScheduledStepsSchemaTest extends NoTransactionWPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected $wpdb;

    protected function setUp(): void
    {
        parent::setUp();

        global $wpdb;

        $this->wpdb = $wpdb;
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Reset the table after each test
        $schema = $this->getSchema();
        $this->dropTable($schema->getTableName());
        $schema->createTable();
    }

    protected function getHandler(): DBTableSchemaHandlerInterface
    {
        $handler = new DBTableSchemaHandler($this->wpdb);

        return $handler;
    }

    protected function getHooks()
    {
        return Stub::makeEmpty(HooksFacade::class, [
            'add_action' => function () {
                return true;
            },
            'remove_action' => function () {
                return true;
            },
        ]);
    }

    protected function getSchema()
    {
        return new WorkflowScheduledStepsSchema($this->getHandler(), $this->getHooks());
    }

    public function testGetTableName(): void
    {
        $schema = $this->getSchema();
        $prefix = $this->getTablePrefix();

        $this->assertEquals($prefix . 'ppfuture_workflow_scheduled_steps', $schema->getTableName());
    }

    public function testCreateTable(): void
    {
        $schema = $this->getSchema();

        $this->dropTable($schema->getTableName());
        $this->assertTrue($schema->createTable());
        $this->assertTableExists($schema->getTableName());
    }

    public function testDropTable(): void
    {
        $schema = $this->getSchema();
        $this->assertTableExists($schema->getTableName());
        $this->assertTrue($schema->dropTable());
        $this->assertTableDoesNotExists($schema->getTableName());
    }

    public function testIsTableHealthyWhenTableIsHealthy(): void
    {
        $schema = $this->getSchema();
        $this->assertTrue($schema->isTableHealthy());
    }

    public function testIsTableHealthyReturnsFalseWhenTableDoesNotExists(): void
    {
        $schema = $this->getSchema();
        $this->dropTable($schema->getTableName());
        $this->assertTableDoesNotExists($schema->getTableName());

        $this->assertFalse($schema->isTableHealthy());
    }

    public function testIsTableHealthyReturnsFalseWhenIndexesAreMissing(): void
    {
        $schema = $this->getSchema();
        $this->dropTableIndex($schema->getTableName(), 'workflow_id');
        $this->assertFalse($schema->isTableHealthy());
    }

    public function testIsTableHealthyReturnsFalseWhenIndexesAreDifferent(): void
    {
        $schema = $this->getSchema();
        $this->dropTableIndex($schema->getTableName(), 'workflow_id');
        $this->createTableIndex($schema->getTableName(), 'workflow_id', ['step_id']);
        $this->assertFalse($schema->isTableHealthy());
    }

    public function testIsTableExistent(): void
    {
        $schema = $this->getSchema();
        $this->assertTrue($schema->isTableExistent());
    }

    public function testIsTableExistentReturnsFalseWhenTableDoesNotExists(): void
    {
        $schema = $this->getSchema();
        $this->dropTable($schema->getTableName());
        $this->assertTableDoesNotExists($schema->getTableName());

        $this->assertFalse($schema->isTableExistent());
    }

    public function testGetErrorsReturnsEmptyArrayWhenTableIsHealthy(): void
    {
        $schema = $this->getSchema();
        $this->assertTrue($schema->isTableHealthy());
        $this->assertEmpty($schema->getErrors());
    }

    public function testGetErrorsReturnsErrorsWhenTableIsNotHealthy(): void
    {
        $schema = $this->getSchema();
        $this->dropTableIndex($schema->getTableName(), 'workflow_id');
        $this->assertFalse($schema->isTableHealthy());

        $errors = $schema->getErrors();
        $this->assertNotEmpty($errors);
    }

    public function testFixTableWhenTableDoesntExists(): void
    {
        $schema = $this->getSchema();
        $this->dropTable($schema->getTableName());
        $this->assertFalse($schema->isTableExistent());

        $schema->fixTable();
        $this->assertTrue($schema->isTableExistent());
    }

    public function testFixTableWhenIndexesAreMissing(): void
    {
        $schema = $this->getSchema();
        $this->dropTableIndex($schema->getTableName(), 'workflow_id');
        $this->assertFalse($schema->isTableHealthy());

        $schema->fixTable();
        $this->assertTrue($schema->isTableHealthy());
    }

    public function testFixTableWhenIndexesAreDifferent(): void
    {
        $schema = $this->getSchema();
        $this->dropTableIndex($schema->getTableName(), 'workflow_id');
        $this->createTableIndex($schema->getTableName(), 'workflow_id', ['step_id']);
        $this->assertFalse($schema->isTableHealthy());

        $schema->fixTable();
        $this->assertTrue($schema->isTableHealthy());
    }

    public function testFixTableMultipleTimes(): void
    {
        $schema = $this->getSchema();
        $this->createTableIndex($schema->getTableName(), 'new_step_id', ['step_id']);
        $this->assertFalse($schema->isTableHealthy());

        $schema->fixTable();
        $this->assertTrue($schema->isTableHealthy());

        $schema->fixTable();
        $this->assertTrue($schema->isTableHealthy());
    }

    public function testDefaultValues(): void
    {
        $schema = $this->getSchema();
        $this->dropTable($schema->getTableName());
        $this->assertTrue($schema->createTable());

        $columns = $this->wpdb->get_results("SHOW COLUMNS FROM {$schema->getTableName()}");
        $columnDefaults = array_column($columns, 'Default', 'Field');

        // Test default values
        $this->assertEquals('0', $columnDefaults['is_recurring']);
        $this->assertEquals('forever', $columnDefaults['repeat_until']);
        $this->assertEquals('0', $columnDefaults['repeat_times']);
        $this->assertEquals('0', $columnDefaults['is_compressed']);
        $this->assertEquals('current_timestamp()', $columnDefaults['created_at']);
        $this->assertNull($columnDefaults['post_id']);
    }

    public function testIndexDefinitions(): void
    {
        $schema = $this->getSchema();
        $this->dropTable($schema->getTableName());
        $this->assertTrue($schema->createTable());

        $indexes = $this->wpdb->get_results("SHOW INDEX FROM {$schema->getTableName()}");
        $indexNames = array_unique(array_column($indexes, 'Key_name'));

        // Test required indexes exist
        $this->assertContains('PRIMARY', $indexNames);
        $this->assertContains('workflow_id', $indexNames);
        $this->assertContains('step_id', $indexNames);
        $this->assertContains('action_uid_hash', $indexNames);
        $this->assertContains('is_recurring', $indexNames);

        // Test index columns
        foreach ($indexes as $index) {
            if ($index->Key_name === 'PRIMARY') {
                $this->assertEquals('action_id', $index->Column_name);
            } elseif ($index->Key_name === 'workflow_id') {
                $this->assertContains($index->Column_name, ['workflow_id', 'action_id']);
            } elseif ($index->Key_name === 'step_id') {
                $this->assertContains($index->Column_name, ['step_id', 'action_id']);
            } elseif ($index->Key_name === 'action_uid_hash') {
                $this->assertContains($index->Column_name, ['action_uid_hash', 'action_id']);
            } elseif ($index->Key_name === 'is_recurring') {
                $this->assertContains($index->Column_name, ['is_recurring', 'action_id']);
            } elseif ($index->Key_name === 'post_id') {
                $this->assertContains($index->Column_name, ['post_id', 'workflow_id', 'action_id']);
            }
        }
    }
}
