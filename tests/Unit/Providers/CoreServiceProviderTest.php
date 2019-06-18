<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Providers;

use League\Container\Container;
use League\Container\ReflectionContainer;
use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Contracts\Renderers\Renderer as RendererContract;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\BasicTestGenerator;
use PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator;
use PhpUnitGen\Core\Generators\Mocks\PhpUnitMockGenerator;
use PhpUnitGen\Core\Parsers\CodeParser;
use PhpUnitGen\Core\Providers\CoreServiceProvider;
use PhpUnitGen\Core\Renderers\Renderer;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class CoreServiceProviderTest.
 *
 * @covers \PhpUnitGen\Core\Providers\CoreServiceProvider
 */
class CoreServiceProviderTest extends TestCase
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new Container();
        $this->container->delegate(new ReflectionContainer());
    }

    public function testItProvidesContractsImplementations(): void
    {
        $config = new Config([
            'mockWith'     => 'phpunit',
            'generateWith' => 'basic',
        ]);

        $this->container->addServiceProvider(new CoreServiceProvider($config));

        $this->assertSame($config, $this->container->get(Config::class));
        $this->assertInstanceOf(
            CodeParser::class,
            $this->container->get(CodeParserContract::class)
        );
        $this->assertInstanceOf(
            Renderer::class,
            $this->container->get(RendererContract::class)
        );
    }

    public function testItThrowsAnExceptionWhenResolvingUnknownMockGenerator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unknown mock generator cannot be resolved');

        $this->container->addServiceProvider(new CoreServiceProvider(new Config([
            'mockWith'     => 'unknown',
            'generateWith' => 'basic',
        ])));

        $this->container->get(MockGenerator::class);
    }

    public function testItProvidesPhpUnitMockGenerator(): void
    {
        $this->container->addServiceProvider(new CoreServiceProvider(new Config([
            'mockWith'     => 'phpunit',
            'generateWith' => 'basic',
        ])));

        $this->assertInstanceOf(
            PhpUnitMockGenerator::class,
            $this->container->get(MockGenerator::class)
        );
    }

    public function testItProvidesMockeryMockGenerator(): void
    {
        $this->container->addServiceProvider(new CoreServiceProvider(new Config([
            'mockWith'     => 'mockery',
            'generateWith' => 'basic',
        ])));

        $this->assertInstanceOf(
            MockeryMockGenerator::class,
            $this->container->get(MockGenerator::class)
        );
    }

    public function testItProvidesCustomMockGenerator(): void
    {
        $serviceProvider = new CoreServiceProvider(new Config([
            'mockWith'     => 'custom',
            'generateWith' => 'basic',
        ]));
        $serviceProvider->addMockGeneratorResolver('custom', function (Container $container) {
            $container->add(MockGenerator::class, StubMockGenerator::class);
        });

        $this->container->addServiceProvider($serviceProvider);

        $this->assertInstanceOf(
            StubMockGenerator::class,
            $this->container->get(MockGenerator::class)
        );
    }

    public function testItThrowsAnExceptionWhenResolvingUnknownTestGenerator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unknown test generator cannot be resolved');

        $this->container->addServiceProvider(new CoreServiceProvider(new Config([
            'mockWith'     => 'phpunit',
            'generateWith' => 'unknown',
        ])));

        $this->container->get(TestGenerator::class);
    }

    public function testItProvidesBasicTestGenerator(): void
    {
        $this->container->addServiceProvider(new CoreServiceProvider(new Config([
            'mockWith'     => 'phpunit',
            'generateWith' => 'basic',
        ])));

        $this->assertInstanceOf(
            BasicTestGenerator::class,
            $this->container->get(TestGenerator::class)
        );
    }

    public function testItProvidesCustomTestGenerator(): void
    {
        $serviceProvider = new CoreServiceProvider(new Config([
            'mockWith'     => 'phpunit',
            'generateWith' => 'custom',
        ]));
        $serviceProvider->addTestGeneratorResolver('custom', function (Container $container) {
            $container->add(TestGenerator::class, StubTestGenerator::class);
        });

        $this->container->addServiceProvider($serviceProvider);

        $this->assertInstanceOf(
            StubTestGenerator::class,
            $this->container->get(TestGenerator::class)
        );
    }
}

class StubMockGenerator
{
}

class StubTestGenerator
{
}
