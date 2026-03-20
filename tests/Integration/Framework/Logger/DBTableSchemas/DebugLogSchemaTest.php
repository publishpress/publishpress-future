<?php

namespace Tests\Modules\Expirator\Schemas;

use Codeception\Stub;
use PublishPress\Future\Framework\Logger\DBTableSchemas\DebugLogSchema;
use PublishPress\Future\Framework\WordPress\Facade\HooksFacade;
use PublishPress\Future\Framework\Database\DBTableSchemaHandler;
use PublishPress\Future\Framework\Database\Interfaces\DBTableSchemaHandlerInterface;
use Tests\NoTransactionWPTestCase;

class DebugLogSchemaTest extends NoTransactionWPTestCase
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

        // Tests use NoTransactionWPTestCase::dropTable(), not DBTableSchemaHandler::dropTable(),
        // so clear cached existence results before each case.
        DBTableSchemaHandler::clearTableExistenceCache();
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
        return new DebugLogSchema($this->getHandler(), $this->getHooks());
    }

    public function testGetTableName(): void
    {
        $schema = $this->getSchema();
        $prefix = $this->getTablePrefix();

        $this->assertEquals($prefix . 'postexpirator_debug', $schema->getTableName());
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

    public function testIsTableHealthyReturnsTrueWhenIsHealthy(): void
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

    public function testIsTableHealthyReturnsFalseWhenIndexesAreDifferent(): void
    {
        $schema = $this->getSchema();
        $this->createTableIndex($schema->getTableName(), 'blog', ['blog']);
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

    public function testFixTableWhenTableDoesntExists(): void
    {
        $schema = $this->getSchema();
        $this->dropTable($schema->getTableName());
        $this->assertFalse($schema->isTableExistent());

        $schema->fixTable();
        $this->assertTrue($schema->isTableExistent());
    }

    public function testFixTableWhenIndexesAreDifferent(): void
    {
        $schema = $this->getSchema();
        $this->createTableIndex($schema->getTableName(), 'blog', ['blog']);
        $this->assertFalse($schema->isTableHealthy());

        $schema->fixTable();
        $this->assertTrue($schema->isTableHealthy());
    }

    public function testFixTableMultipleTimes(): void
    {
        $schema = $this->getSchema();
        $this->createTableIndex($schema->getTableName(), 'blog', ['blog']);
        $this->assertFalse($schema->isTableHealthy());

        $schema->fixTable();
        $this->assertTrue($schema->isTableHealthy());

        $schema->fixTable();
        $this->assertTrue($schema->isTableHealthy());
    }
}
