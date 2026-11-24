<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace PublishPress\Future\Framework\WordPress\Facade;

use wpdb;

\defined('ABSPATH') || exit;

class DatabaseFacade
{
    /**
     * @var wpdb
     */
    private $wpdb;

    public function __construct(wpdb $wpdb)
    {
        $this->wpdb = $wpdb;
    }

    /**
     * @return string
     */
    public function getTablePrefix()
    {
        return $this->wpdb->prefix;
    }

    /**
     * @param string $query
     * @param int $x
     * @param int $y
     *
     * @return string|null
     */
    public function getVar($query = null, $x = 0, $y = 0)
    {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
        return $this->wpdb->get_var($query, $x, $y);
    }

    /**
     * @param string $query
     *
     * @return string
     */
    public function prepare($query, ...$args)
    {
        $functionArgs = func_get_args();

        return call_user_func_array([$this->wpdb, 'prepare'], $functionArgs);
    }

    public function escape($data)
    {
        return \esc_sql($data);
    }

    /**
     * @param string[]|string
     * @param bool
     *
     * @return array
     */
    public function modifyStructure($queries = '', $execute = true)
    {
        if (! function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        return \dbDelta($queries, $execute);
    }

    /**
     * @param string $query
     * @param string $output
     * @return array|object|null
     */
    public function getResults($query = null, $output = 'OBJECT')
    {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
        return $this->wpdb->get_results($query, $output);
    }

    /**
     * @param string $tableName
     *
     * @return bool
     */
    public function dropTable($tableName): bool
    {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.SchemaChange
        return $this->query(
            $this->prepare(
                'DROP TABLE IF EXISTS %i',
                $tableName
            )
        );
    }

    /**
     * @param string $query
     *
     * @return int|bool
     */
    public function query($query)
    {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.DirectDatabaseQuery.DirectQuery
        return $this->wpdb->query($query);
    }
}
