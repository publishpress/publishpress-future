<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace unit\Core;

use Codeception\Test\Unit;
use PublishPress\Future\Core\Paths;
use UnitTester;

class PathsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @dataProvider baseDirProvider
     */
    public function testGetBaseDirPathReturnsNoTrailingSlash($baseDir, $expected)
    {
        $instance = new Paths($baseDir);
        $result = $instance->getBaseDirPath();

        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function baseDirProvider()
    {
        return [
            ['/tmp', '/tmp'],
            ['/tmp/', '/tmp'],
        ];
    }

    /**
     * @dataProvider baseDirVendorProvider
     */
    public function testGetVendorDirPathReturnsNoTrailingSlash($baseDir, $expected)
    {
        $instance = new Paths($baseDir);
        $result = $instance->getVendorDirPath();

        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function baseDirVendorProvider()
    {
        $libVendorPath = realpath(__DIR__ . '/../../../../lib/vendor');

        return [
            ['/tmp', $libVendorPath],
            ['/tmp/', $libVendorPath],
        ];
    }
}
