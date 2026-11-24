<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace unit\Core\Framework\Logger;

use Codeception\Stub\Expected;
use Codeception\Test\Feature\Stub;
use Codeception\Test\Unit;
use Exception;
use PublishPress\Future\Framework\Database\DBTableSchemaHandler;
use PublishPress\Future\Framework\Logger\Logger;
use PublishPress\Future\Framework\Logger\LogLevelAbstract as LogLevel;
use PublishPress\Future\Framework\WordPress\Facade\DatabaseFacade;
use PublishPress\Future\Framework\WordPress\Facade\SiteFacade;
use PublishPress\Future\Modules\Settings\SettingsFacade;
use UnitTester;

class LoggerTest extends Unit
{
    use Stub;

    /**
     * @var UnitTester
     */
    protected $tested;

    protected function _after()
    {
        Logger::resetDebugTableEnsureCacheForTesting();
        DBTableSchemaHandler::clearTableExistenceCache();

        parent::_after();
    }

    /**
     * @throws Exception
     */
    public function testConstructDoesNotQueryDatabaseForTable()
    {
        $db = $this->makeEmpty(
            DatabaseFacade::class,
            [
                'getVar' => Expected::never(),
                'modifyStructure' => Expected::never(),
                'getTablePrefix' => 'wp_',
                'escape' => function ($string) {
                    return $string;
                },
            ]
        );

        $site = $this->makeEmpty(SiteFacade::class);

        $settings = $this->makeEmpty(SettingsFacade::class);

        $this->construct(
            Logger::class,
            [
                $db,
                $site,
                $settings,
            ]
        );
    }

    /**
     * @throws Exception
     */
    public function testLogCreatesDebugTableWhenMissing()
    {
        $db = $this->makeEmpty(
            DatabaseFacade::class,
            [
                'getVar' => Expected::once(function () {
                    return null;
                }),
                'modifyStructure' => Expected::once(
                    function ($sql) {
                        $this->assertStringStartsWith('CREATE TABLE `wp_postexpirator_debug`', $sql);
                        $this->assertStringContainsString('request_id', $sql);
                        $this->assertStringContainsString('trigger_activated', $sql);
                    }
                ),
                'prepare' => Expected::atLeastOnce(
                    function ($query, ...$args) {
                        $result = $query;
                        foreach ($args as $arg) {
                            $replacement = is_int($arg)
                                ? (string) (int) $arg
                                : "'" . str_replace("'", "''", (string) $arg) . "'";
                            $result = preg_replace('/%[sd]/', $replacement, $result, 1);
                        }

                        return $result;
                    }
                ),
                'query' => Expected::once(),
                'getTablePrefix' => 'wp_',
                'escape' => function ($string) {
                    return $string;
                },
            ]
        );

        $site = $this->makeEmpty(SiteFacade::class, [
            'getBlogId' => 1,
        ]);

        $settings = $this->makeEmpty(SettingsFacade::class, [
            'getDebugIsEnabled' => true,
        ]);

        $logger = $this->construct(
            Logger::class,
            [
                $db,
                $site,
                $settings,
            ]
        );

        $logger->log(LogLevel::DEBUG, 'test message', []);
    }
}
