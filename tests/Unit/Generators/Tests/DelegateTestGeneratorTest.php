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
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Factories\ClassFactory;
use PhpUnitGen\Core\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Factories\PropertyFactory;
use PhpUnitGen\Core\Generators\Factories\StatementFactory;
use PhpUnitGen\Core\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicTestGenerator;
use PhpUnitGen\Core\Generators\Tests\DelegateTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\LaravelTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\UnitClassFactory;
use PhpUnitGen\Core\Models\TestClass;
use Psr\Container\ContainerInterface;
use Roave\BetterReflection\Reflection\ReflectionClass;
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
        $this->testGenerator = $this->getMockBuilder(DelegateTestGenerator::class)
            ->onlyMethods(['makeNewContainer', 'isLaravelProject'])
            ->getMock();
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

    /**
     * @param bool $expected
     * @param bool $isInterface
     * @param bool $isAnonymous
     *
     * @dataProvider canGenerateDataProvider
     */
    public function testCanGenerate(bool $expected, bool $isInterface, bool $isAnonymous): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $reflectionClass->shouldReceive([
            'isInterface' => $isInterface,
            'isAnonymous' => $isAnonymous,
        ]);

        $this->assertSame($expected, $this->testGenerator->canGenerateFor($reflectionClass));
    }

    public function canGenerateDataProvider(): array
    {
        return [
            [false, true, true],
            [false, true, false],
            [false, false, true],
            [true, false, false],
        ];
    }

    public function testGenerateThrowExceptionWhenCannotGenerate(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $reflectionClass->shouldReceive([
            'isInterface' => true,
            'isAnonymous' => false,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot generate tests for given reflection class');

        $this->testGenerator->generate($reflectionClass);
    }

    public function testGenerateDelegatesToLaravelClass(): void
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
                    MethodFactory::class => BasicMethodFactory::class,
                ],
            ],
        ]);

        $this->testGenerator->expects($this->once())
            ->method('isLaravelProject')
            ->willReturn(true);
        $this->testGenerator->expects($this->once())
            ->method('makeNewContainer')
            ->with($this->callback(function (ConfigContract $config) {
                return $config->automaticGeneration() === false
                    && $config->implementations() === [
                        TestGenerator::class                => LaravelTestGenerator::class,
                        ClassFactoryContract::class         => UnitClassFactory::class,
                        DocumentationFactoryContract::class => DocumentationFactory::class,
                        ImportFactoryContract::class        => ImportFactory::class,
                        MethodFactory::class                => BasicMethodFactory::class,
                        PropertyFactoryContract::class      => PropertyFactory::class,
                        StatementFactoryContract::class     => StatementFactory::class,
                        ValueFactoryContract::class         => ValueFactory::class,
                    ];
            }))
            ->willReturn($container);

        $container->shouldReceive('get')
            ->with(TestGenerator::class)
            ->andReturn($delegatedTestGenerator);

        $delegatedTestGenerator->shouldReceive('generate')
            ->with($reflectionClass)
            ->andReturn($testClass);

        $this->assertSame($testClass, $this->testGenerator->generate($reflectionClass));
    }

    public function testGenerateDelegatesToLaravelPolicy(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $container = Mockery::mock(ContainerInterface::class);
        $delegatedTestGenerator = Mockery::mock(TestGenerator::class);
        $testClass = Mockery::mock(TestClass::class);

        $reflectionClass->shouldReceive([
            'isInterface' => false,
            'isAnonymous' => false,
            'getName'     => 'App\\Policies\\ProductPolicy',
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

        $this->testGenerator->expects($this->once())
            ->method('isLaravelProject')
            ->willReturn(true);
        $this->testGenerator->expects($this->once())
            ->method('makeNewContainer')
            ->with($this->callback(function (ConfigContract $config) {
                return $config->automaticGeneration() === false
                    && $config->implementations() === [
                        TestGenerator::class                => PolicyTestGenerator::class,
                        ClassFactoryContract::class         => UnitClassFactory::class,
                        DocumentationFactoryContract::class => DocumentationFactory::class,
                        ImportFactoryContract::class        => ImportFactory::class,
                        MethodFactory::class                => BasicMethodFactory::class,
                        PropertyFactoryContract::class      => PropertyFactory::class,
                        StatementFactoryContract::class     => StatementFactory::class,
                        ValueFactoryContract::class         => ValueFactory::class,
                    ];
            }))
            ->willReturn($container);

        $container->shouldReceive('get')
            ->with(TestGenerator::class)
            ->andReturn($delegatedTestGenerator);

        $delegatedTestGenerator->shouldReceive('generate')
            ->with($reflectionClass)
            ->andReturn($testClass);

        $this->assertSame($testClass, $this->testGenerator->generate($reflectionClass));
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

        $this->testGenerator->expects($this->once())
            ->method('isLaravelProject')
            ->willReturn(false);
        $this->testGenerator->expects($this->once())
            ->method('makeNewContainer')
            ->with($this->callback(function (ConfigContract $config) {
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
            ->willReturn($container);

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
}
