<?php

/**
 * Copyright (c) 2025, Ramble Ventures
 */

namespace unit\Core\DI;

use Codeception\Test\Feature\Stub;
use Codeception\Test\Unit;
use Exception;
use PublishPress\Future\Core\DI\ServiceProvider;
use UnitTester;

class ServiceProviderTest extends Unit
{
    use Stub;

    /**
     * @var UnitTester
     */
    protected $tested;

    /**
     * @throws Exception
     */
    public function testGetFactories()
    {
        $serviceProvider = new ServiceProvider(
            [
                'service-a' => function () {
                    return 'a';
                },

                'service-b' => function () {
                    return 'b';
                },
            ]
        );

        $factories = $serviceProvider->getFactories();

        $this->assertIsArray($factories);
        $this->assertCount(2, $factories);
        $this->assertIsCallable($factories['service-a']);
        $this->assertIsCallable($factories['service-b']);
    }
}
