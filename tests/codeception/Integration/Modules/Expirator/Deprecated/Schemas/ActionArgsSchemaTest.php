<?php

namespace Tests\Modules\Expirator\Deprecated\Schemas;

use PublishPress\Future\Modules\Expirator\Deprecated\Schemas\ActionArgsSchema;
use Tests\NoTransactionWPTestCase;

class ActionArgsSchemaTest extends NoTransactionWPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected $tableName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tableName = ActionArgsSchema::getTableName();
        $this->resetTable();
    }

    protected function resetTable(): void
    {
        $this->dropTable($this->tableName);
        ActionArgsSchema::createTable();
    }

    public function testGetTableName(): void
    {
        $tableName = ActionArgsSchema::getTableName();
        $prefix = $this->getTablePrefix();

        $this->assertEquals($prefix . 'ppfuture_actions_args', $tableName);
    }

    public function testCreateTable(): void
    {
        $this->dropTable($this->tableName);
        $this->assertTrue(ActionArgsSchema::createTable());
    }

    public function testTableExistsReturnsFalseIfTableDoesNotExist(): void
    {
        $this->dropTable($this->tableName);
        $this->assertFalse(ActionArgsSchema::isTableExistent());
    }

    public function testTableExistsReturnsTrueIfTableExists(): void
    {
        $this->assertTrue(ActionArgsSchema::isTableExistent(), 'Table should exist by default');
    }

    public function testIsTableHealthyReturnsTrueIfTableIsHealthy(): void
    {
        $isHealthy = ActionArgsSchema::isTableHealthy();
        $this->assertTrue($isHealthy);
    }

    public function testIsTableHealthyReturnsFalseIfTableDoesNotExist(): void
    {
        $this->dropTable($this->tableName);
        $this->assertFalse(ActionArgsSchema::isTableHealthy());
    }

    public function testIsTableHealthyReturnsFalseIfIndexesAreMissing(): void
    {
        $this->dropTableIndex($this->tableName, 'cron_action_id');
        $this->assertFalse(ActionArgsSchema::isTableHealthy());
    }

    public function testIsTableHealthyReturnsFalseIfIndexesExistsButIsDifferent(): void
    {
        $this->dropTableIndex($this->tableName, 'cron_action_id');
        $this->createTableIndex($this->tableName, 'cron_action_id', ['post_id']);

        $this->assertFalse(ActionArgsSchema::isTableHealthy());

        // To fix the index for next tests
        $this->dropTable($this->tableName);
    }

    public function testIsTableHealthyReturnsFalseIfColumnArgsLengthIsNotUpdated(): void
    {
        $this->modifyColumnTable($this->tableName, 'args', 'VARCHAR(255)');
        $this->assertFalse(ActionArgsSchema::isTableHealthy());
    }

    public function testFixTableCreatesTableIfItDoesNotExist(): void
    {
        $this->dropTable($this->tableName);
        ActionArgsSchema::fixTable();

        $this->assertTrue(ActionArgsSchema::isTableExistent());
    }

    public function testFixTableUpdatesColumnArgsLengthIfItIsNotUpdated(): void
    {
        $this->modifyColumnTable($this->tableName, 'args', 'VARCHAR(255)');

        ActionArgsSchema::fixTable();

        $this->assertTrue(ActionArgsSchema::isTableHealthy());
    }

    public function testFixTableCreatesIndexesIfTheyAreMissing(): void
    {
        $this->dropTableIndex($this->tableName, 'post_id');

        ActionArgsSchema::fixTable();

        $isHealthy = ActionArgsSchema::isTableHealthy();
        $this->assertTrue($isHealthy);
    }

    public function testFixTableCreatesIndexesIfColumnsAreDifferent(): void
    {
        $this->dropTableIndex($this->tableName, 'cron_action_id');
        $this->createTableIndex($this->tableName, 'cron_action_id', ['post_id']);

        ActionArgsSchema::fixTable();

        $this->assertTrue(ActionArgsSchema::isTableHealthy());
    }

    public function testDropTable(): void
    {
        $this->assertTableExists($this->tableName);
        ActionArgsSchema::dropTable();
        $this->assertTableDoesNotExists($this->tableName);
    }

    public function testGetErrors(): void
    {
        $this->dropTable($this->tableName);
        $this->assertFalse(ActionArgsSchema::isTableHealthy());

        $errors = ActionArgsSchema::getErrors();

        $this->assertContains('The table wp_ppfuture_actions_args does not exist.', $errors);
    }

    public function testDeprecatedMethodsExists(): void
    {
        $this->assertClassMethodExists(ActionArgsSchema::class, 'tableExists');
        $this->assertClassMethodExists(ActionArgsSchema::class, 'healthCheckTableExists');
        $this->assertClassMethodExists(ActionArgsSchema::class, 'checkSchemaHealth');
        $this->assertClassMethodExists(ActionArgsSchema::class, 'createTableIfNotExists');
        $this->assertClassMethodExists(ActionArgsSchema::class, 'fixSchema');
        $this->assertClassMethodExists(ActionArgsSchema::class, 'getSchemaHealthErrors');
    }
}
