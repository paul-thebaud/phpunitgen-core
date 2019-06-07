<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Tests\Unit\Providers;

use League\Container\Container;
use PHPUnit\Framework\TestCase;
use PhpUnitGen\Core\Contracts\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Parsers\CodeParser;
use PhpUnitGen\Core\Providers\CoreServiceProvider;

/**
 * Class CoreServiceProviderTest.
 *
 * @covers CoreServiceProvider
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
