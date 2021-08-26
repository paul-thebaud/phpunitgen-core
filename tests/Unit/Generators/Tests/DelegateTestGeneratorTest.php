<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests;

use League\Container\Container;
use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\MockObject\MockObject;
use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Contracts\Config\Config as ConfigContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ClassFactory as ClassFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory as DocumentationFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory as ImportFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\PropertyFactory as PropertyFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory as StatementFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory as ValueFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator;
use PhpUnitGen\Core\Exceptions\RuntimeException;
use PhpUnitGen\Core\Generators\Factories\ClassFactory;
use PhpUnitGen\Core\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Factories\PropertyFactory;
use PhpUnitGen\Core\Generators\Factories\StatementFactory;
use PhpUnitGen\Core\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicTestGenerator;
use PhpUnitGen\Core\Generators\Tests\DelegateTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Channel\ChannelTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Command\CommandTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Controller\ControllerTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\FeatureClassFactory;
use PhpUnitGen\Core\Generators\Tests\Laravel\Job\JobTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\LaravelTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Listener\ListenerTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Resource\ResourceTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Rule\RuleTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\UnitClassFactory;
use PhpUnitGen\Core\Models\TestClass;
use Psr\Container\ContainerInterface;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class DelegateTestGeneratorTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\DelegateTestGenerator
 */
class DelegateTestGeneratorTest extends TestCase
{
    /**
     * @var ConfigContract|Mock
     */
    protected $config;

    /**
     * @var DelegateTestGenerator|MockObject
     */
    protected $testGenerator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(ConfigContract::class);
        $this->testGenerator = Mockery::mock(DelegateTestGenerator::class)
            ->shouldAllowMockingProtectedMethods()
            ->makePartial();
        $this->testGenerator->setConfig($this->config);
    }

    public function testImplementations(): void
    {
        $implementations = DelegateTestGenerator::implementations();

        $this->assertSame([
            TestGenerator::class => DelegateTestGenerator::class,
        ], $implementations);

        foreach ($implementations as $contract => $implementation) {
            $this->assertArrayHasKey($contract, class_implements($implementation));
        }
    }

    public function testCanGenerate(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $this->assertSame(true, $this->testGenerator->canGenerateFor($reflectionClass));
    }

    /**
     * @param string $class
     * @param string $expectedGenerator
     * @param string $expectedClassFactory
     *
     * @dataProvider generateDelegatesToLaravelDataProvider
     */
    public function testGenerateDelegatesToLaravel(
        string $class,
        string $expectedGenerator,
        string $expectedClassFactory
    ): void {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $container = Mockery::mock(ContainerInterface::class);
        $delegatedTestGenerator = Mockery::mock(TestGenerator::class);
        $testClass = Mockery::mock(TestClass::class);

        $reflectionClass->shouldReceive([
            'isInterface' => false,
            'isAnonymous' => false,
            'getName'     => $class,
        ]);

        $this->config->shouldReceive([
            'toArray' => [
                'automaticGeneration' => false,
                'implementations'     => [
                    TestGenerator::class => DelegateTestGenerator::class,
                    MethodFactory::class => BasicMethodFactory::class,
                ],
            ],
        ]);
        $this->config->shouldReceive('getOption')
            ->with('context')
            ->andReturn('laravel');

        $this->testGenerator->shouldReceive('makeNewContainer')
            ->once()
            ->with(Mockery::on(function (ConfigContract $config) use ($expectedGenerator, $expectedClassFactory) {
                return $config->automaticGeneration() === false
                    && $config->implementations() === [
                        TestGenerator::class                => $expectedGenerator,
                        ClassFactoryContract::class         => $expectedClassFactory,
                        DocumentationFactoryContract::class => DocumentationFactory::class,
                        ImportFactoryContract::class        => ImportFactory::class,
                        MethodFactory::class                => BasicMethodFactory::class,
                        PropertyFactoryContract::class      => PropertyFactory::class,
                        StatementFactoryContract::class     => StatementFactory::class,
                        ValueFactoryContract::class         => ValueFactory::class,
                    ];
            }))
            ->andReturn($container);

        $container->shouldReceive('get')
            ->with(TestGenerator::class)
            ->andReturn($delegatedTestGenerator);

        $delegatedTestGenerator->shouldReceive('generate')
            ->with($reflectionClass)
            ->andReturn($testClass);

        $this->assertSame($testClass, $this->testGenerator->generate($reflectionClass));
    }

    public function generateDelegatesToLaravelDataProvider(): array
    {
        return [
            ['App\\Services\\ProductService', LaravelTestGenerator::class, UnitClassFactory::class],
            ['App\\Broadcasting\\ProductChannel', ChannelTestGenerator::class, UnitClassFactory::class],
            ['App\\Console\\Commands\\PruneOldProducts', CommandTestGenerator::class, FeatureClassFactory::class],
            ['App\\Http\\Controllers\\ProductController', ControllerTestGenerator::class, FeatureClassFactory::class],
            ['App\\Jobs\\SendNewProduct', JobTestGenerator::class, UnitClassFactory::class],
            ['App\\Listeners\\NewProductListener', ListenerTestGenerator::class, UnitClassFactory::class],
            ['App\\Policies\\ProductPolicy', PolicyTestGenerator::class, UnitClassFactory::class],
            ['App\\Http\\Resources\\ProductResource', ResourceTestGenerator::class, UnitClassFactory::class],
            ['App\\Rules\\ProductIsPublicRule', RuleTestGenerator::class, UnitClassFactory::class],
            ['App\\Policies\\ProductPolicy', PolicyTestGenerator::class, UnitClassFactory::class],
        ];
    }

    public function testGenerateDelegatesToBasic(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $container = Mockery::mock(ContainerInterface::class);
        $delegatedTestGenerator = Mockery::mock(TestGenerator::class);
        $testClass = Mockery::mock(TestClass::class);

        $reflectionClass->shouldReceive([
            'isInterface' => false,
            'isAnonymous' => false,
            'getName'     => 'App\\Services\\ProductService',
        ]);

        $this->config->shouldReceive([
            'toArray' => [
                'automaticGeneration' => false,
                'implementations'     => [
                    TestGenerator::class => DelegateTestGenerator::class,
                    MethodFactory::class => PolicyMethodFactory::class,
                ],
            ],
        ]);
        $this->config->shouldReceive('getOption')
            ->with('context')
            ->andReturn(null);

        $this->testGenerator->shouldReceive('makeNewContainer')
            ->once()
            ->with(Mockery::on(function (ConfigContract $config) {
                return $config->automaticGeneration() === false
                    && $config->implementations() === [
                        TestGenerator::class                => BasicTestGenerator::class,
                        ClassFactoryContract::class         => ClassFactory::class,
                        DocumentationFactoryContract::class => DocumentationFactory::class,
                        ImportFactoryContract::class        => ImportFactory::class,
                        MethodFactory::class                => PolicyMethodFactory::class,
                        PropertyFactoryContract::class      => PropertyFactory::class,
                        StatementFactoryContract::class     => StatementFactory::class,
                        ValueFactoryContract::class         => ValueFactory::class,
                    ];
            }))
            ->andReturn($container);

        $container->shouldReceive('get')
            ->with(TestGenerator::class)
            ->andReturn($delegatedTestGenerator);

        $delegatedTestGenerator->shouldReceive('generate')
            ->with($reflectionClass)
            ->andReturn($testClass);

        $this->assertSame($testClass, $this->testGenerator->generate($reflectionClass));
    }

    public function testMakeNewContainer(): void
    {
        $config = Config::make();

        $container = $this->callProtectedMethod(new DelegateTestGenerator(), 'makeNewContainer', $config);

        $this->assertInstanceOf(Container::class, $container);
        $this->assertSame($config, $container->get(ConfigContract::class));
    }

    public function testGetClassFactory(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('getClassFactory method should not be called on a DelegateTestGenerator');

        $this->testGenerator->getClassFactory();
    }
}
