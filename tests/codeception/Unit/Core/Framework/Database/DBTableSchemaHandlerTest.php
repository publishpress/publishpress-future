<?php

/**
 * Unit tests for DBTableSchemaHandler table existence caching.
 *
 * @package     PublishPress\Future
 * @author      PublishPress
 * @copyright   Copyright (c) 2026, PublishPress
 * @license     GPLv2 or later
 */

namespace unit\Core\Framework\Database;

use Codeception\Test\Unit;
use PublishPress\Future\Framework\Database\DBTableSchemaHandler;

/**
 * Minimal wpdb double counting get_var calls.
 */
final class WpdbTableExistenceStub
{
    /**
     * @var string
     */
    public $prefix = 'wp_';

    /**
     * @var int
     */
    public $getVarCallCount = 0;

    /**
     * @param string               $query
     * @param mixed                ...$args
     * @return string
     */
    public function prepare($query, ...$args)
    {
        $sql = $query;
        foreach ($args as $arg) {
            $sql = preg_replace('/%s/', "'" . str_replace("'", "\\'", (string) $arg) . "'", $sql, 1);
        }

        return $sql;
    }

    /**
     * @param string|null $query
     * @param int         $x
     * @param int         $y
     * @return string|null
     */
    public function get_var($query = null, $x = 0, $y = 0)
    {
        $this->getVarCallCount++;

        return '1';
    }
}

class DBTableSchemaHandlerTest extends Unit
{
    protected function _after(): void
    {
        DBTableSchemaHandler::clearTableExistenceCache();

        parent::_after();
    }

    /**
     * @return void
     */
    public function testSecondIsTableExistentDoesNotCallGetVarAgain(): void
    {
        $wpdb = new WpdbTableExistenceStub();
        $handler = new DBTableSchemaHandler($wpdb);
        $handler->setTableName('posts');

        $this->assertTrue($handler->isTableExistent());
        $this->assertSame(1, $wpdb->getVarCallCount);

        $this->assertTrue($handler->isTableExistent());
        $this->assertSame(1, $wpdb->getVarCallCount);
    }

    /**
     * @return void
     */
    public function testClearTableExistenceCacheForcesAnotherLookup(): void
    {
        $wpdb = new WpdbTableExistenceStub();
        $handler = new DBTableSchemaHandler($wpdb);
        $handler->setTableName('posts');

        $handler->isTableExistent();
        $handler->isTableExistent();
        $this->assertSame(1, $wpdb->getVarCallCount);

        DBTableSchemaHandler::clearTableExistenceCache();
        $handler->isTableExistent();
        $this->assertSame(2, $wpdb->getVarCallCount);
    }
}
