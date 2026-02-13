<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace unit\Core\Framework\Logger;

use Codeception\Stub\Expected;
use Codeception\Test\Feature\Stub;
use Codeception\Test\Unit;
use Exception;
use PublishPress\Future\Framework\Logger\Logger;
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

    /**
     * @throws Exception
     */
    public function testConstructCreatesDBTable()
    {
        $db = $this->makeEmpty(
            DatabaseFacade::class,
            [
                'modifyStructure' => Expected::once(
                    function ($sql) {
                        $this->assertStringStartsWith('CREATE TABLE `wp_postexpirator_debug`', $sql);
                        $this->assertStringContainsString('request_id', $sql);
                    }
                ),
                'escape' => function ($string) {
                    return $string;
                },
                'getTablePrefix' => 'wp_',
            ]
        );

        $site = $this->makeEmpty(SiteFacade::class);

        $settings = $this->makeEmpty(SettingsFacade::class);

        $logger = $this->construct(
            Logger::class,
            [
                $db,
                $site,
                $settings,
            ]
        );
    }
}
