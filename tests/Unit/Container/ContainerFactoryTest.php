<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Container;

use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Container\ContainerFactory;
use PhpUnitGen\Core\Contracts\Config\Config as ConfigContract;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class ContainerFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Container\ContainerFactory
 */
class ContainerFactoryTest extends TestCase
{
    public function testMake(): void
    {
        $config = Config::make();

        $container = ContainerFactory::make($config);

        $this->assertSame($config, $container->get(ConfigContract::class));
    }
}
