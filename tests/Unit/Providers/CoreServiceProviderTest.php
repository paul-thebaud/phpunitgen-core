<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Providers;

use League\Container\Container;
use League\Container\ReflectionContainer;
use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Aware\ClassFactoryAwareTrait;
use PhpUnitGen\Core\Aware\ConfigAwareTrait;
use PhpUnitGen\Core\Aware\DocumentationFactoryAwareTrait;
use PhpUnitGen\Core\Aware\ImportFactoryAwareTrait;
use PhpUnitGen\Core\Aware\MethodFactoryAwareTrait;
use PhpUnitGen\Core\Aware\MockGeneratorAwareTrait;
use PhpUnitGen\Core\Aware\PropertyFactoryAwareTrait;
use PhpUnitGen\Core\Aware\StatementFactoryAwareTrait;
use PhpUnitGen\Core\Aware\TestGeneratorAwareTrait;
use PhpUnitGen\Core\Aware\ValueFactoryAwareTrait;
use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Contracts\Aware\ClassFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\ConfigAware;
use PhpUnitGen\Core\Contracts\Aware\DocumentationFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\ImportFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\MethodFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\MockGeneratorAware;
use PhpUnitGen\Core\Contracts\Aware\PropertyFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\StatementFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\TestGeneratorAware;
use PhpUnitGen\Core\Contracts\Aware\ValueFactoryAware;
use PhpUnitGen\Core\Contracts\Config\Config as ConfigContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ClassFactory as ClassFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory as DocumentationFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory as ImportFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory as MethodFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\PropertyFactory as PropertyFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory as StatementFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory as ValueFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator as MockGeneratorContract;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator as TestGeneratorContract;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Contracts\Renderers\Renderer as RendererContract;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator;
use PhpUnitGen\Core\Generators\Tests\DelegateTestGenerator;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Parsers\CodeParser;
use PhpUnitGen\Core\Providers\CoreServiceProvider;
use PhpUnitGen\Core\Renderers\Renderer;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class CoreServiceProviderTest.
 *
 * @covers \PhpUnitGen\Core\Aware\ClassFactoryAwareTrait
 * @covers \PhpUnitGen\Core\Aware\ConfigAwareTrait
 * @covers \PhpUnitGen\Core\Aware\DocumentationFactoryAwareTrait
 * @covers \PhpUnitGen\Core\Aware\ImportFactoryAwareTrait
 * @covers \PhpUnitGen\Core\Aware\MethodFactoryAwareTrait
 * @covers \PhpUnitGen\Core\Aware\MockGeneratorAwareTrait
 * @covers \PhpUnitGen\Core\Aware\PropertyFactoryAwareTrait
 * @covers \PhpUnitGen\Core\Aware\StatementFactoryAwareTrait
 * @covers \PhpUnitGen\Core\Aware\TestGeneratorAwareTrait
 * @covers \PhpUnitGen\Core\Aware\ValueFactoryAwareTrait
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

    public function testWhenAllOkWithDefaultConfiguration(): void
    {
        $this->config->shouldReceive('implementations')->andReturn(
            Config::make()->implementations()
        );

        $this->coreServiceProvider->register();

        $this->assertInstanceOf(CodeParser::class, $this->container->get(CodeParserContract::class));
        $this->assertInstanceOf(MockeryMockGenerator::class, $this->container->get(MockGeneratorContract::class));
        $this->assertInstanceOf(Renderer::class, $this->container->get(RendererContract::class));
        $this->assertInstanceOf(DelegateTestGenerator::class, $this->container->get(TestGeneratorContract::class));
    }

    public function testAllInflectorsAreDefined(): void
    {
        $this->config->shouldReceive('implementations')->andReturn(
            Config::make()->implementations()
        );

        $this->container->delegate(new ReflectionContainer());

        /** @var StubAware $stubAware */
        $stubAware = $this->container->get(StubAware::class);

        $this->assertInstanceOf(ClassFactoryContract::class, $stubAware->getClassFactory());
        $this->assertInstanceOf(ConfigContract::class, $stubAware->getConfig());
        $this->assertInstanceOf(DocumentationFactoryContract::class, $stubAware->getDocumentationFactory());
        $this->assertInstanceOf(ImportFactoryContract::class, $stubAware->getImportFactory());
        $this->assertInstanceOf(MethodFactoryContract::class, $stubAware->getMethodFactory());
        $this->assertInstanceOf(MockGeneratorContract::class, $stubAware->getMockGenerator());
        $this->assertInstanceOf(PropertyFactoryContract::class, $stubAware->getPropertyFactory());
        $this->assertInstanceOf(StatementFactoryContract::class, $stubAware->getStatementFactory());
        $this->assertInstanceOf(TestGeneratorContract::class, $stubAware->getTestGenerator());
        $this->assertInstanceOf(ValueFactoryContract::class, $stubAware->getValueFactory());
    }
}

class InvalidStubTestGenerator
{
}

class InvalidConstructorStubTestGenerator implements TestGenerator
{
    public static function implementations(): array
    {
        return [];
    }

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
    public static function implementations(): array
    {
        return [];
    }

    public function generate(ReflectionClass $reflectionClass): TestClass
    {
    }

    public function canGenerateFor(ReflectionClass $reflectionClass): bool
    {
    }
}

class StubAware implements
    ClassFactoryAware,
    ConfigAware,
    DocumentationFactoryAware,
    ImportFactoryAware,
    MethodFactoryAware,
    MockGeneratorAware,
    PropertyFactoryAware,
    StatementFactoryAware,
    TestGeneratorAware,
    ValueFactoryAware
{
    use ClassFactoryAwareTrait;
    use ConfigAwareTrait;
    use DocumentationFactoryAwareTrait;
    use ImportFactoryAwareTrait;
    use MethodFactoryAwareTrait;
    use MockGeneratorAwareTrait;
    use PropertyFactoryAwareTrait;
    use StatementFactoryAwareTrait;
    use TestGeneratorAwareTrait;
    use ValueFactoryAwareTrait;
}
