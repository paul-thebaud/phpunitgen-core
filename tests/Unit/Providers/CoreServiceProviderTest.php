<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Providers;

use League\Container\Container;
use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Contracts\Config\Config as ConfigContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory as ImportFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator as MockGeneratorContract;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator as TestGeneratorContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory as ValueFactoryContract;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Contracts\Renderers\Renderer as RendererContract;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator;
use PhpUnitGen\Core\Generators\Tests\BasicTestGenerator;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Parsers\CodeParser;
use PhpUnitGen\Core\Providers\CoreServiceProvider;
use PhpUnitGen\Core\Renderers\Renderer;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class CoreServiceProviderTest.
 *
 * @covers \PhpUnitGen\Core\Providers\CoreServiceProvider
 */
class CoreServiceProviderTest extends TestCase
{
    /**
     * @var ConfigContract|Mock
     */
    protected $config;

    /**
     * @var CoreServiceProvider
     */
    protected $coreServiceProvider;

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

        $this->config = Mockery::mock(ConfigContract::class);

        $this->coreServiceProvider = new CoreServiceProvider($this->config);
        $this->container = (new Container())->addServiceProvider($this->coreServiceProvider);
    }

    public function testWhenContractIsNotAllowed(): void
    {
        $this->config->shouldReceive('implementations')->andReturn([
            'StubInvalidContract' => 'StudInvalidConcrete',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('contract StubInvalidContract implementation is not necessary');

        $this->coreServiceProvider->register();
    }

    public function testWhenConcreteDoesNotExists(): void
    {
        $this->config->shouldReceive('implementations')->andReturn([
            TestGenerator::class => 'StudInvalidConcrete',
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('class StudInvalidConcrete does not exists');

        $this->coreServiceProvider->register();
    }

    public function testWhenConcreteDoesImplementsContract(): void
    {
        $this->config->shouldReceive('implementations')->andReturn([
            TestGenerator::class => InvalidStubTestGenerator::class,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'class '.InvalidStubTestGenerator::class.' does not implements '.TestGenerator::class
        );

        $this->coreServiceProvider->register();
    }

    public function testWhenConcreteHasInvalidConstructorParameters(): void
    {
        $this->config->shouldReceive('implementations')->andReturn([
            TestGenerator::class => InvalidConstructorStubTestGenerator::class,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'dependency dependency for class '.InvalidConstructorStubTestGenerator::class.' has an unresolvable type'
        );

        $this->coreServiceProvider->register();
    }

    public function testWhenMissingDefinitions(): void
    {
        $this->config->shouldReceive('implementations')->andReturn([
            TestGenerator::class => StubTestGenerator::class,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing contract implementation in config');

        $this->coreServiceProvider->register();
    }

    public function testWhenAllOkWithDefaultConfiguration(): void
    {
        $this->config->shouldReceive('implementations')->andReturn(
            Config::make()->implementations()
        );

        $this->coreServiceProvider->register();

        $this->assertInstanceOf(CodeParser::class, $this->container->get(CodeParserContract::class));
        $this->assertInstanceOf(ImportFactory::class, $this->container->get(ImportFactoryContract::class));
        $this->assertInstanceOf(MockeryMockGenerator::class, $this->container->get(MockGeneratorContract::class));
        $this->assertInstanceOf(Renderer::class, $this->container->get(RendererContract::class));
        $this->assertInstanceOf(BasicTestGenerator::class, $this->container->get(TestGeneratorContract::class));
        $this->assertInstanceOf(ValueFactory::class, $this->container->get(ValueFactoryContract::class));
    }
}

class InvalidStubTestGenerator
{
}

class InvalidConstructorStubTestGenerator implements TestGenerator
{
    public function __construct($dependency)
    {
    }

    public function generate(ReflectionClass $reflectionClass): TestClass
    {
    }

    public function canGenerateFor(ReflectionClass $reflectionClass): bool
    {
    }
}

class StubTestGenerator implements TestGenerator
{
    public function generate(ReflectionClass $reflectionClass): TestClass
    {
    }

    public function canGenerateFor(ReflectionClass $reflectionClass): bool
    {
    }
}
