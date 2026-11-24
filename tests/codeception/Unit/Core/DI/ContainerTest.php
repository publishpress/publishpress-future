<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace unit\Core\DI;

use Codeception\Test\Feature\Stub;
use Codeception\Test\Unit;
use PublishPress\Future\Core\DI\Container;
use PublishPress\Future\Core\DI\NotCallableException;
use PublishPress\Future\Core\DI\ServiceNotFoundException;
use stdClass;
use UnitTester;

class ContainerTest extends Unit
{
    use Stub;

    /**
     * @var UnitTester
     */
    protected $tester;

    public function testHasReturningTrueForExistentService()
    {
        $services = [
            'version' => '0.1.0',
            'module' => new stdClass(),
        ];

        $container = $this->construct(
            Container::class,
            [$services]
        );

        $this->assertTrue($container->has('version'));
        $this->assertTrue($container->has('module'));
    }

    public function testGetReturnsResolvedService()
    {
        $services = [
            'version' => '0.1.0',
            'module' => static function () {
                return new stdClass();
            }
        ];

        $container = $this->construct(
            Container::class,
            [$services]
        );

        $serviceVersion = $container->get('version');

        $this->assertIsString($serviceVersion);
        $this->assertEquals($services['version'], $serviceVersion);

        $serviceModule = $container->get('module');

        $this->assertIsObject($serviceModule);
        $this->assertEquals(new stdClass(), $serviceModule);
    }

    public function testGetReturnsCachedService()
    {
        $services = [
            'module' => static function () {
                return new stdClass();
            }
        ];

        $container = $this->construct(
            Container::class,
            [$services]
        );

        $serviceModule1 = $container->get('module');
        $serviceModule2 = $container->get('module');

        $this->assertIsObject($serviceModule1);
        $this->assertIsObject($serviceModule2);
        $this->assertSame($serviceModule1, $serviceModule2);
    }

    public function testBuildInvokesCachedFactoryWithArgsAndReturnsResult()
    {
        $services = [
            'factory' => static function () {
                return static function ($name, $count) {
                    return $name . '-' . $count;
                };
            },
        ];

        $container = $this->construct(
            Container::class,
            [$services]
        );

        $result = $container->build('factory', 'widget', 2);

        $this->assertSame('widget-2', $result);
    }

    public function testBuildReturnsDifferentInstancesWhileGetReturnsSameCallable()
    {
        $outerCalls = 0;

        $services = [
            'factory' => static function () use (&$outerCalls) {
                $outerCalls++;

                return static function () {
                    return new stdClass();
                };
            },
        ];

        $container = $this->construct(
            Container::class,
            [$services]
        );

        $factory = $container->get('factory');
        $first = $container->build('factory');
        $second = $container->build('factory');

        $this->assertSame($factory, $container->get('factory'));
        $this->assertIsCallable($factory);
        $this->assertNotSame($first, $second);
        $this->assertSame(1, $outerCalls);
    }

    public function testBuildThrowsNotCallableExceptionForNonCallableService()
    {
        $services = [
            'version' => '0.1.0',
        ];

        $container = $this->construct(
            Container::class,
            [$services]
        );

        $this->expectException(NotCallableException::class);
        $this->expectExceptionMessage("Service 'version' is not callable");

        $container->build('version');
    }

    public function testBuildThrowsServiceNotFoundExceptionForMissingId()
    {
        $container = $this->construct(
            Container::class,
            [[]]
        );

        $this->expectException(ServiceNotFoundException::class);

        $container->build('missing');
    }
}
