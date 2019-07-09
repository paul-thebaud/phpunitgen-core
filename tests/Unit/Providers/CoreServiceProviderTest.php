<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Providers;

use League\Container\Container;
use League\Container\ReflectionContainer;
use Mockery;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\ImportFactory as ImportFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator;
use PhpUnitGen\Core\Contracts\Generators\ValueFactory as ValueFactoryContract;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Contracts\Renderers\Renderer as RendererContract;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator;
use PhpUnitGen\Core\Generators\Mocks\PhpUnitMockGenerator;
use PhpUnitGen\Core\Generators\Tests\BasicTestGenerator;
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
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('mockWith')->andReturn('mockery');
        $config->shouldReceive('generateWith')->andReturn('basic');

        $this->container->addServiceProvider(new CoreServiceProvider($config));

        $this->assertSame($config, $this->container->get(Config::class));
        $this->assertInstanceOf(
            CodeParser::class,
            $this->container->get(CodeParserContract::class)
        );
        $this->assertInstanceOf(
            ImportFactory::class,
            $this->container->get(ImportFactoryContract::class)
        );
        $this->assertInstanceOf(
            Renderer::class,
            $this->container->get(RendererContract::class)
        );
        $this->assertInstanceOf(
            ValueFactory::class,
            $this->container->get(ValueFactoryContract::class)
        );
    }

    public function testItThrowsAnExceptionWhenResolvingUnknownMockGenerator(): void
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('mockWith')->andReturn('unknown');
        $config->shouldReceive('generateWith')->andReturn('basic');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unknown mock generator cannot be resolved');

        $this->container->addServiceProvider(new CoreServiceProvider($config));

        $this->container->get(MockGenerator::class);
    }

    public function testItProvidesPhpUnitMockGenerator(): void
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('mockWith')->andReturn('phpunit');
        $config->shouldReceive('generateWith')->andReturn('basic');

        $this->container->addServiceProvider(new CoreServiceProvider($config));

        $this->assertInstanceOf(
            PhpUnitMockGenerator::class,
            $this->container->get(MockGenerator::class)
        );
    }

    public function testItProvidesMockeryMockGenerator(): void
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('mockWith')->andReturn('mockery');
        $config->shouldReceive('generateWith')->andReturn('basic');

        $this->container->addServiceProvider(new CoreServiceProvider($config));

        $this->assertInstanceOf(
            MockeryMockGenerator::class,
            $this->container->get(MockGenerator::class)
        );
    }

    public function testItProvidesCustomMockGenerator(): void
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('mockWith')->andReturn('custom');
        $config->shouldReceive('generateWith')->andReturn('basic');

        $serviceProvider = new CoreServiceProvider($config);
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
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('mockWith')->andReturn('mockery');
        $config->shouldReceive('generateWith')->andReturn('unknown');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('unknown test generator cannot be resolved');

        $this->container->addServiceProvider(new CoreServiceProvider($config));

        $this->container->get(TestGenerator::class);
    }

    public function testItProvidesBasicTestGenerator(): void
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('mockWith')->andReturn('mockery');
        $config->shouldReceive('generateWith')->andReturn('basic');

        $this->container->addServiceProvider(new CoreServiceProvider($config));

        $this->assertInstanceOf(
            BasicTestGenerator::class,
            $this->container->get(TestGenerator::class)
        );
    }

    public function testItProvidesCustomTestGenerator(): void
    {
        $config = Mockery::mock(Config::class);
        $config->shouldReceive('mockWith')->andReturn('mockery');
        $config->shouldReceive('generateWith')->andReturn('custom');

        $serviceProvider = new CoreServiceProvider($config);
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
