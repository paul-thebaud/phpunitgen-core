<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Tests\Unit\Providers;

use League\Container\Container;
use PHPUnit\Framework\TestCase;
use PhpUnitGen\Core\Contracts\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Parsers\CodeParser;
use PhpUnitGen\Core\Providers\PhpUnitGenCoreServiceProvider;

/**
 * Class PhpUnitGenCoreServiceProviderTest.
 *
 * @covers PhpUnitGenCoreServiceProvider
 */
class PhpUnitGenCoreServiceProviderTest extends TestCase
{
    public function testItProvidesCodeParserContractImplementation(): void
    {
        $container = new Container();

        $container->addServiceProvider(new PhpUnitGenCoreServiceProvider());

        $this->assertInstanceOf(CodeParser::class, $container->get(CodeParserContract::class));
    }
}
