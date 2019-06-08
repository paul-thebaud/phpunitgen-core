<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Providers;

use League\Container\Container;
use Tests\PhpUnitGen\Core\TestCase;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Parsers\CodeParser;
use PhpUnitGen\Core\Providers\CoreServiceProvider;

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

        $container->addServiceProvider(new CoreServiceProvider());

        $this->assertInstanceOf(CodeParser::class, $container->get(CodeParserContract::class));
    }
}
