<?php

use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\ServicesAbstract as Services;
use PublishPress\Future\Modules\Debug\DebugInterface;

defined('ABSPATH') || exit;

/**
 * The class that adds debug entries to the database.
 *
 * @deprecated 2.8.0
 */
// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class PostExpiratorDebug
{
    /**
     * @var DebugInterface
     */
    private $debug;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->debug = Container::getInstance()->get(Services::DEBUG);
    }

    /**
     * Drop Database Table.
     */
    public function removeDBTable()
    {
        $this->debug->dropDatabaseTable();
    }

    /**
     * Insert into Database Table.
     */
    public function save($data)
    {
        $this->debug->log($data['message']);
    }

    /**
     * Get the HTML of the table's data.
     */
    public function getTable()
    {
        return $this->debug->fetchAll();
    }

    /**
     * Truncate Database Table.
     */
    public function purge()
    {
        $this->debug->deleteLogs();
    }
}
