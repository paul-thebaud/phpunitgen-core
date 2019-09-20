<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Container;

use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Container\CoreContainerFactory;
use PhpUnitGen\Core\Contracts\Config\Config as ConfigContract;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class CoreContainerFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Container\CoreContainerFactory
 */
class CoreContainerFactoryTest extends TestCase
{
    public function testMake(): void
    {
        $config = Config::make();

        $container = CoreContainerFactory::make($config);

        $this->assertSame($config, $container->get(ConfigContract::class));
    }
}
