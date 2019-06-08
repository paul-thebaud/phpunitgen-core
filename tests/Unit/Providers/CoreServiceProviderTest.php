<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Providers;

use League\Container\Container;
use League\Container\ReflectionContainer;
use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Parsers\CodeParser;
use PhpUnitGen\Core\Providers\CoreServiceProvider;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class CoreServiceProviderTest.
 *
 * @covers \PhpUnitGen\Core\Providers\CoreServiceProvider
 */
class CoreServiceProviderTest extends TestCase
{
    public function testItProvidesCodeParserContractImplementation(): void
    {
        $container = new Container();
        $container->delegate(new ReflectionContainer());

        $container->addServiceProvider(new CoreServiceProvider(new Config()));

        $this->assertInstanceOf(CodeParser::class, $container->get(CodeParserContract::class));
    }
}
