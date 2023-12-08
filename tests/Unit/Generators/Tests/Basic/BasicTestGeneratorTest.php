<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Basic;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\Factories\ClassFactory as ClassFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory as DocumentationFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory as ImportFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory as MethodFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\PropertyFactory as PropertyFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory as StatementFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory as ValueFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator as TestGeneratorContract;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Factories\ClassFactory;
use PhpUnitGen\Core\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Factories\PropertyFactory;
use PhpUnitGen\Core\Generators\Factories\StatementFactory;
use PhpUnitGen\Core\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicTestGenerator;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class BasicTestGeneratorTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\Basic\BasicTestGenerator
 * @covers \PhpUnitGen\Core\Generators\Tests\AbstractTestGenerator
 */
class BasicTestGeneratorTest extends TestCase
{
    /**
     * @var ClassFactoryContract|Mock
     */
    protected $classFactory;

    /**
     * @var Config|Mock
     */
    protected $config;

    /**
     * @var ImportFactoryContract|Mock
     */
    protected $importFactory;

    /**
     * @var MethodFactoryContract|Mock
     */
    protected $methodFactory;

    /**
     * @var PropertyFactoryContract|Mock
     */
    protected $propertyFactory;

    /**
     * @var BasicTestGenerator
     */
    protected $testGenerator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->classFactory = Mockery::mock(ClassFactoryContract::class);
        $this->config = Mockery::mock(Config::class);
        $this->importFactory = Mockery::mock(ImportFactoryContract::class);
        $this->methodFactory = Mockery::mock(MethodFactoryContract::class);
        $this->propertyFactory = Mockery::mock(PropertyFactoryContract::class);
        $this->testGenerator = new BasicTestGenerator();
        $this->testGenerator->setClassFactory($this->classFactory);
        $this->testGenerator->setConfig($this->config);
        $this->testGenerator->setImportFactory($this->importFactory);
        $this->testGenerator->setMethodFactory($this->methodFactory);
        $this->testGenerator->setPropertyFactory($this->propertyFactory);
    }

    public function testImplementations(): void
    {
        $implementations = BasicTestGenerator::implementations();

        self::assertSame([
            TestGeneratorContract::class        => BasicTestGenerator::class,
            ClassFactoryContract::class         => ClassFactory::class,
            DocumentationFactoryContract::class => DocumentationFactory::class,
            ImportFactoryContract::class        => ImportFactory::class,
            MethodFactoryContract::class        => BasicMethodFactory::class,
            PropertyFactoryContract::class      => PropertyFactory::class,
            StatementFactoryContract::class     => StatementFactory::class,
            ValueFactoryContract::class         => ValueFactory::class,
        ], $implementations);

        foreach ($implementations as $contract => $implementation) {
            self::assertArrayHasKey($contract, class_implements($implementation));
        }
    }

    /**
     * @param bool $expected
     * @param bool $isInterface
     * @param bool $isAnonymous
     * @param bool $hasMethod
     *
     * @dataProvider canGenerateDataProvider
     */
    public function testCanGenerate(bool $expected, bool $isInterface, bool $isAnonymous, array $methods = []): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $reflectionClass->shouldReceive([
            'isInterface' => $isInterface,
            'isAnonymous' => $isAnonymous,
        ]);

        if (! $isInterface && ! $isAnonymous) {
            $this->config->shouldReceive([
                'excludedMethods' => [],
            ]);

            $this->classFactory->shouldReceive('make')
                ->once()
                ->with($reflectionClass)
                ->andReturn(new TestClass($reflectionClass, 'FooTest'));

            $reflectionClass->shouldReceive('getImmediateMethods')
                ->once()
                ->andReturn($methods);
        }

        self::assertSame($expected, $this->testGenerator->canGenerateFor($reflectionClass));
    }

    public function canGenerateDataProvider(): array
    {
        $publicMethod = Mockery::mock(ReflectionMethod::class);
        $publicMethod->shouldReceive([
            'isPublic'     => true,
            'getShortName' => 'foo',
        ]);
        $privateMethod = Mockery::mock(ReflectionMethod::class);
        $privateMethod->shouldReceive([
            'isPublic'     => false,
            'getShortName' => 'foo',
        ]);

        return [
            [false, true, true],
            [false, true, false],
            [false, false, true],
            [false, false, false, [$privateMethod]],
            [true, false, false, [$publicMethod]],
            [true, false, false, [$privateMethod, $publicMethod]],
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

    public function testGenerateWithAutomaticGeneration(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionConstructor = Mockery::mock(ReflectionMethod::class);
        $reflectionGetter = Mockery::mock(ReflectionMethod::class);
        $reflectionSetter = Mockery::mock(ReflectionMethod::class);
        $reflectionPublicMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionProtectedMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionBarParameter = Mockery::mock(ReflectionParameter::class);
        $reflectionBarProperty = Mockery::mock(ReflectionProperty::class);
        $classProperty = Mockery::mock(TestProperty::class);
        $barProperty = Mockery::mock(TestProperty::class);
        $setUpMethod = Mockery::mock(TestMethod::class);
        $tearDownMethod = Mockery::mock(TestMethod::class);
        $getterMethod = Mockery::mock(TestMethod::class);
        $setterMethod = Mockery::mock(TestMethod::class);
        $publicMethod = Mockery::mock(TestMethod::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'isInterface'            => false,
            'isAnonymous'            => false,
            'getName'                => 'App\\Foo',
            'getImmediateMethods'    => [
                $reflectionConstructor,
                $reflectionGetter,
                $reflectionSetter,
                $reflectionPublicMethod,
                $reflectionProtectedMethod,
            ],
            'getImmediateProperties' => [
                $reflectionBarProperty,
            ],
        ]);

        $reflectionConstructor->shouldReceive([
            'getShortName'  => '__construct',
            'isPublic'      => true,
            'isAbstract'    => false,
            'isStatic'      => false,
            'getParameters' => [$reflectionBarParameter],
        ]);
        $reflectionGetter->shouldReceive([
            'getDeclaringClass' => $reflectionClass,
            'getShortName'      => 'getBar',
            'isPublic'          => true,
            'isStatic'          => false,
        ]);
        $reflectionSetter->shouldReceive([
            'getDeclaringClass' => $reflectionClass,
            'getShortName'      => 'setBar',
            'isPublic'          => true,
            'isStatic'          => false,
        ]);
        $reflectionPublicMethod->shouldReceive([
            'getDeclaringClass' => $reflectionClass,
            'getShortName'      => 'setBaz',
            'isPublic'          => true,
            'isStatic'          => false,
        ]);
        $reflectionProtectedMethod->shouldReceive([
            'getDeclaringClass' => $reflectionClass,
            'getShortName'      => 'getBaz',
            'isPublic'          => false,
            'isStatic'          => false,
        ]);

        $reflectionBarParameter->shouldReceive([
            'getName' => 'bar',
        ]);

        $reflectionBarProperty->shouldReceive([
            'getName'  => 'bar',
            'isStatic' => false,
        ]);

        $this->config->shouldReceive([
            'automaticGeneration' => true,
            'excludedMethods'     => ['__construct'],
            'testCase'            => 'Tests\\TestCase',
        ]);

        $this->classFactory->shouldReceive('make')
            ->with($reflectionClass)
            ->andReturn($class);

        $this->importFactory->shouldReceive('make')
            ->with($class, 'Tests\\TestCase')
            ->andReturn(Mockery::mock(TestImport::class));
        $this->importFactory->shouldReceive('make')
            ->with($class, 'App\\Foo')
            ->andReturn(Mockery::mock(TestImport::class));

        $this->propertyFactory->shouldReceive('makeForClass')
            ->with($class)
            ->andReturn($classProperty);
        $this->propertyFactory->shouldReceive('makeForParameter')
            ->with($class, $reflectionBarParameter)
            ->andReturn($barProperty);

        $classProperty->shouldReceive('setTestClass')
            ->with($class)
            ->andReturnSelf();
        $barProperty->shouldReceive('setTestClass')
            ->with($class)
            ->andReturnSelf();

        $this->methodFactory->shouldReceive('makeSetUp')
            ->with($class)
            ->andReturn($setUpMethod);
        $this->methodFactory->shouldReceive('makeTearDown')
            ->with($class)
            ->andReturn($tearDownMethod);
        $this->methodFactory->shouldReceive('makeTestable')
            ->with($class, $reflectionGetter)
            ->andReturn($getterMethod);
        $this->methodFactory->shouldReceive('makeTestable')
            ->with($class, $reflectionSetter)
            ->andReturn($setterMethod);
        $this->methodFactory->shouldReceive('makeIncomplete')
            ->with($reflectionPublicMethod)
            ->andReturn($publicMethod);

        $setUpMethod->shouldReceive('setTestClass')
            ->with($class)
            ->andReturnSelf();
        $tearDownMethod->shouldReceive('setTestClass')
            ->with($class)
            ->andReturnSelf();
        $publicMethod->shouldReceive('setTestClass')
            ->with($class)
            ->andReturnSelf();

        $returnedClass = $this->testGenerator->generate($reflectionClass);

        self::assertSame($class, $returnedClass);
        self::assertCount(2, $returnedClass->getProperties());
        self::assertSame($classProperty, $returnedClass->getProperties()->get(0));
        self::assertSame($barProperty, $returnedClass->getProperties()->get(1));
        self::assertCount(3, $returnedClass->getMethods());
        self::assertSame($setUpMethod, $returnedClass->getMethods()->get(0));
        self::assertSame($tearDownMethod, $returnedClass->getMethods()->get(1));
        self::assertSame($publicMethod, $returnedClass->getMethods()->get(2));
    }

    public function testGenerateWithoutConstructor(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionPublicMethod = Mockery::mock(ReflectionMethod::class);
        $classProperty = Mockery::mock(TestProperty::class);
        $setUpMethod = Mockery::mock(TestMethod::class);
        $tearDownMethod = Mockery::mock(TestMethod::class);
        $publicMethod = Mockery::mock(TestMethod::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'isInterface'            => false,
            'isAnonymous'            => false,
            'getName'                => 'App\\Foo',
            'getImmediateMethods'    => [
                $reflectionPublicMethod,
            ],
            'getImmediateProperties' => [],
        ]);

        $reflectionPublicMethod->shouldReceive([
            'getDeclaringClass' => $reflectionClass,
            'getShortName'      => 'setBaz',
            'isPublic'          => true,
        ]);

        $this->config->shouldReceive([
            'automaticGeneration' => true,
            'excludedMethods'     => ['__construct'],
            'testCase'            => 'Tests\\TestCase',
        ]);

        $this->classFactory->shouldReceive('make')
            ->with($reflectionClass)
            ->andReturn($class);

        $this->importFactory->shouldReceive('make')
            ->with($class, 'Tests\\TestCase')
            ->andReturn(Mockery::mock(TestImport::class));
        $this->importFactory->shouldReceive('make')
            ->with($class, 'App\\Foo')
            ->andReturn(Mockery::mock(TestImport::class));

        $this->propertyFactory->shouldReceive('makeForClass')
            ->with($class)
            ->andReturn($classProperty);

        $classProperty->shouldReceive('setTestClass')
            ->with($class)
            ->andReturnSelf();

        $this->methodFactory->shouldReceive('makeSetUp')
            ->with($class)
            ->andReturn($setUpMethod);
        $this->methodFactory->shouldReceive('makeTearDown')
            ->with($class)
            ->andReturn($tearDownMethod);
        $this->methodFactory->shouldReceive('makeIncomplete')
            ->with($reflectionPublicMethod)
            ->andReturn($publicMethod);

        $setUpMethod->shouldReceive('setTestClass')
            ->with($class)
            ->andReturnSelf();
        $tearDownMethod->shouldReceive('setTestClass')
            ->with($class)
            ->andReturnSelf();
        $publicMethod->shouldReceive('setTestClass')
            ->with($class)
            ->andReturnSelf();

        $returnedClass = $this->testGenerator->generate($reflectionClass);

        self::assertSame($class, $returnedClass);
        self::assertCount(1, $returnedClass->getProperties());
        self::assertSame($classProperty, $returnedClass->getProperties()->get(0));
        self::assertCount(3, $returnedClass->getMethods());
        self::assertSame($setUpMethod, $returnedClass->getMethods()->get(0));
        self::assertSame($tearDownMethod, $returnedClass->getMethods()->get(1));
        self::assertSame($publicMethod, $returnedClass->getMethods()->get(2));
    }

    public function testGenerateWithoutAutomaticGeneration(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionPublicMethod = Mockery::mock(ReflectionMethod::class);
        $classProperty = Mockery::mock(TestProperty::class);
        $publicMethod = Mockery::mock(TestMethod::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'isInterface'         => false,
            'isAnonymous'         => false,
            'getName'             => 'App\\Foo',
            'getImmediateMethods' => [
                $reflectionPublicMethod,
            ],
        ]);

        $reflectionPublicMethod->shouldReceive([
            'getDeclaringClass' => $reflectionClass,
            'getShortName'      => 'setBaz',
            'isPublic'          => true,
        ]);

        $this->config->shouldReceive([
            'automaticGeneration' => false,
            'excludedMethods'     => ['__construct'],
            'testCase'            => 'Tests\\TestCase',
        ]);

        $this->classFactory->shouldReceive('make')
            ->with($reflectionClass)
            ->andReturn($class);

        $this->importFactory->shouldReceive('make')
            ->with($class, 'Tests\\TestCase')
            ->andReturn(Mockery::mock(TestImport::class));
        $this->importFactory->shouldReceive('make')
            ->with($class, 'App\\Foo')
            ->andReturn(Mockery::mock(TestImport::class));

        $this->propertyFactory->shouldReceive('makeForClass')
            ->with($class)
            ->andReturn($classProperty);

        $classProperty->shouldReceive('setTestClass')
            ->with($class)
            ->andReturnSelf();

        $this->methodFactory->shouldReceive('makeIncomplete')
            ->with($reflectionPublicMethod)
            ->andReturn($publicMethod);

        $publicMethod->shouldReceive('setTestClass')
            ->with($class)
            ->andReturnSelf();

        $returnedClass = $this->testGenerator->generate($reflectionClass);

        self::assertSame($class, $returnedClass);
        self::assertCount(0, $returnedClass->getProperties());
        self::assertCount(1, $returnedClass->getMethods());
        self::assertSame($publicMethod, $returnedClass->getMethods()->get(0));
    }

    public function testGetClassFactory(): void
    {
        self::assertSame($this->classFactory, $this->testGenerator->getClassFactory());
    }
}
