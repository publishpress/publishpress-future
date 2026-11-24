<?php

namespace Tests;

use PublishPress\Future\Framework\Database\DBTableSchemaHandler;

class NoTransactionWPTestCase extends \lucatume\WPBrowser\TestCase\WPTestCase
{
    protected static $ignore_files;
    protected static $hooks_saved = array();

    /**
     * Override the parent method to prevent the transaction from being started.
     * We need this so we can test the database schema in the plugins.
     * After any test the DB is restored, so there is no need to worry about the transaction.
     */
    /**
     * Runs the routine before each test is executed.
     */
    public function set_up()
    {
        set_time_limit(0);

        $this->factory = static::factory();

        if (! self::$ignore_files) {
            self::$ignore_files = $this->scan_user_uploads();
        }

        if (! self::$hooks_saved) {
            $this->_backup_hooks();
        }

        global $wp_rewrite;

        $this->clean_up_global_scope();

        /*
         * When running core tests, ensure that post types and taxonomies
         * are reset for each test. We skip this step for non-core tests,
         * given the large number of plugins that register post types and
         * taxonomies at 'init'.
         */
        if (defined('WP_RUN_CORE_TESTS') && WP_RUN_CORE_TESTS) {
            $this->reset_post_types();
            $this->reset_taxonomies();
            $this->reset_post_statuses();
            $this->reset__SERVER();

            if ($wp_rewrite->permalink_structure) {
                $this->set_permalink_structure('');
            }
        }

        // $this->start_transaction();
        $this->expectDeprecated();
        add_filter('wp_die_handler', array( $this, 'get_wp_die_handler' ));
    }
    /**
     * After a test method runs, resets any state in WordPress the test method might have changed.
     */
    public function tear_down()
    {
        global $wpdb, $wp_the_query, $wp_query, $wp;

        // $wpdb->query( 'ROLLBACK' );

        if (is_multisite()) {
            while (ms_is_switched()) {
                restore_current_blog();
            }
        }

        // Reset query, main query, and WP globals similar to wp-settings.php.
        $wp_the_query = new \WP_Query();
        $wp_query     = $wp_the_query;
        $wp           = new \WP();

        // Reset globals related to the post loop and `setup_postdata()`.
        $post_globals = array( 'post', 'id', 'authordata', 'currentday', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages' );
        foreach ($post_globals as $global) {
            $GLOBALS[ $global ] = null;
        }

        /*
         * Reset globals related to current screen to provide a consistent global starting state
         * for tests that interact with admin screens. Replaces the need for individual tests
         * to invoke `set_current_screen( 'front' )` (or an alternative implementation) as a reset.
         *
         * The globals are from `WP_Screen::set_current_screen()`.
         *
         * Why not invoke `set_current_screen( 'front' )`?
         * Performance (faster test runs with less memory usage). How so? For each test,
         * it saves creating an instance of WP_Screen, making two method calls,
         * and firing of the `current_screen` action.
         */
        $current_screen_globals = array( 'current_screen', 'taxnow', 'typenow' );
        foreach ($current_screen_globals as $global) {
            $GLOBALS[ $global ] = null;
        }

        // Reset comment globals.
        $comment_globals = array( 'comment_alt', 'comment_depth', 'comment_thread_alt' );
        foreach ($comment_globals as $global) {
            $GLOBALS[ $global ] = null;
        }

        /*
         * Reset $wp_sitemap global so that sitemap-related dynamic $wp->public_query_vars
         * are added when the next test runs.
         */
        $GLOBALS['wp_sitemaps'] = null;

        // Reset template globals.
        $GLOBALS['wp_stylesheet_path'] = null;
        $GLOBALS['wp_template_path']   = null;

        $this->unregister_all_meta_keys();
        remove_theme_support('html5');
        remove_filter('query', array( $this, '_create_temporary_tables' ));
        remove_filter('query', array( $this, '_drop_temporary_tables' ));
        remove_filter('wp_die_handler', array( $this, 'get_wp_die_handler' ));
        $this->_restore_hooks();
        wp_set_current_user(0);

        $this->reset_lazyload_queue();
    }

    public function rollback_transaction()
    {
        global $wpdb;

        $wpdb->query('ROLLBACK');
    }

    // Custom methods to test the database schema
    protected function getTablePrefix(): string
    {
        $loaderConfig = $this->getModule('lucatume\WPBrowser\Module\WPLoader')->_getConfig();

        return $loaderConfig['tablePrefix'];
    }

    protected function getTableNames(): array
    {
        global $wpdb;
        $tables = $wpdb->get_results('SHOW TABLES');
        $tables = array_map('current', $tables);

        return $tables;
    }

    protected function dropTable($tableName): void
    {
        global $wpdb;
        $wpdb->query('DROP TABLE IF EXISTS `' . $tableName . '`');
        DBTableSchemaHandler::clearTableExistenceCache($tableName);
    }

    protected function dropTableIndex($tableName, $indexName): void
    {
        global $wpdb;
        $wpdb->query("ALTER TABLE `$tableName` DROP INDEX `$indexName`");
    }

    protected function createTableIndex($tableName, $indexName, $columns): void
    {
        global $wpdb;
        $columns = implode(', ', $columns);
        $wpdb->query("ALTER TABLE `$tableName` ADD INDEX `$indexName` ($columns)");
    }

    protected function assertTableDoesNotExists($tableName): void
    {
        $tables = $this->getTableNames();

        $this->assertNotContains($tableName, $tables);
    }

    protected function assertTableExists($tableName): void
    {
        $tables = $this->getTableNames();

        $this->assertContains($tableName, $tables);
    }

    protected function assertClassMethodExists($className, $methodName): void
    {
        $this->assertTrue(method_exists($className, $methodName));
    }

    protected function modifyColumnTable($tableName, $columnName, $columnType): void
    {
        global $wpdb;
        $wpdb->query("ALTER TABLE `$tableName` MODIFY COLUMN `$columnName` $columnType");
    }

    public function createTable(string $tableName, string $tableStructure): void
    {
        global $wpdb;
        $charsetCollate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE `$tableName` (
			$tableStructure
		) $charsetCollate;";

        $wpdb->query($sql);
    }

    private function getColumns($tableName): array
    {
        global $wpdb;
        $columns = $wpdb->get_results("SHOW COLUMNS FROM `$tableName`");
        return array_column($columns, 'Field');
    }

    protected function assertColumnExists($tableName, $columnName): void
    {
        $columnNames = $this->getColumns($tableName);

        $this->assertContains($columnName, $columnNames, "Column `$columnName` does not exist in table `$tableName`");
    }

    protected function assertColumnDoesNotExists($tableName, $columnName): void
    {
        $columnNames = $this->getColumns($tableName);

        $this->assertNotContains($columnName, $columnNames, "Column `$columnName` exists in table `$tableName`");
    }
}
