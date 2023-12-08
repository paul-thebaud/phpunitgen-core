<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Factories;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\TypeFactory;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
use PhpUnitGen\Core\Generators\Factories\PropertyFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestProperty;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionType;
use Tests\PhpUnitGen\Core\Helpers\PhpVersionDependents;
use Tests\PhpUnitGen\Core\TestCase;
use Tightenco\Collect\Support\Collection;

/**
 * Class PropertyFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Factories\PropertyFactory
 */
class PropertyFactoryTest extends TestCase
{
    /**
     * @var Config|Mock
     */
    protected $config;

    /**
     * @var DocumentationFactory|Mock
     */
    protected $documentationFactory;

    /**
     * @var TypeFactory|Mock
     */
    protected $typeFactory;

    /**
     * @var MockGenerator|Mock
     */
    protected $mockGenerator;

    /**
     * @var PropertyFactory
     */
    protected $propertyFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(Config::class);
        $this->documentationFactory = Mockery::mock(DocumentationFactory::class);
        $this->typeFactory = Mockery::mock(TypeFactory::class);
        $this->mockGenerator = Mockery::mock(MockGenerator::class);
        $this->propertyFactory = new PropertyFactory();
        $this->propertyFactory->setConfig($this->config);
        $this->propertyFactory->setDocumentationFactory($this->documentationFactory);
        $this->propertyFactory->setTypeFactory($this->typeFactory);
        $this->propertyFactory->setMockGenerator($this->mockGenerator);
    }

    public function testMakeForClassWithoutTypedProperties(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $import = Mockery::mock(TestImport::class);
        $doc = Mockery::mock(TestDocumentation::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'getShortName' => 'Foo',
            'getName'      => 'App\\Foo',
        ]);

        $this->config->shouldReceive('testClassTypedProperties')
            ->andReturnFalse();

        $this->typeFactory->shouldReceive('makeFromString')
            ->with($class, 'App\\Foo', false)
            ->andReturn($import);

        $this->documentationFactory->shouldReceive('makeForProperty')
            ->with(
                Mockery::type(TestProperty::class),
                Mockery::on(fn (Collection $types) => $types->toArray() === [$import])
            )
            ->andReturn($doc);

        $property = $this->propertyFactory->makeForClass($class);

        self::assertSame('foo', $property->getName());
        self::assertSame($doc, $property->getDocumentation());
        self::assertSame(null, $property->getType());
    }

    public function testMakeForClassWithTypedProperties(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $import = Mockery::mock(TestImport::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'getShortName' => 'Foo',
            'getName'      => 'App\\Foo',
        ]);

        $this->config->shouldReceive('testClassTypedProperties')
            ->andReturnTrue();

        $this->typeFactory->shouldReceive('makeFromString')
            ->with($class, 'App\\Foo', false)
            ->andReturn($import);
        $this->typeFactory->shouldReceive('formatTypes')
            ->with(Mockery::on(fn (Collection $types) => $types->toArray() === [$import]))
            ->andReturn('Foo');

        $property = $this->propertyFactory->makeForClass($class);

        self::assertSame('foo', $property->getName());
        self::assertSame(null, $property->getDocumentation());
        self::assertSame('Foo', $property->getType());
    }

    public function testMakeForParameterWithObjectType(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionParameter = Mockery::mock(ReflectionParameter::class);
        $reflectionType = PhpVersionDependents::makeReflectionTypeMock();
        $doc = Mockery::mock(TestDocumentation::class);
        $import = new TestImport('App\\Bar');
        $mockImport = Mockery::mock(TestImport::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'getName' => 'App\\Foo',
        ]);

        $reflectionMethod->shouldReceive([
            'getDocComment' => '',
        ]);

        $reflectionParameter->shouldReceive([
            'getName'              => 'bar',
            'getType'              => $reflectionType,
            'getDeclaringFunction' => $reflectionMethod,
        ]);

        $reflectionType->shouldReceive([
            '__toString' => 'App\\Bar',
            'isBuiltIn'  => false,
            'allowsNull' => false,
        ]);

        $this->config->shouldReceive('testClassTypedProperties')
            ->andReturnFalse();

        $this->typeFactory->shouldReceive('makeFromString')
            ->with($class, 'App\\Bar', false)
            ->andReturn($import);

        $this->mockGenerator->shouldReceive('getMockType')
            ->with($class)
            ->andReturn($mockImport);

        $this->documentationFactory->shouldReceive('makeForProperty')
            ->with(
                Mockery::type(TestProperty::class),
                Mockery::on(function (Collection $typeHints) use ($import, $mockImport) {
                    return $typeHints->toArray() === [$import, $mockImport];
                })
            )
            ->andReturn($doc);

        $property = $this->propertyFactory->makeForParameter($class, $reflectionParameter);

        self::assertSame('bar', $property->getName());
        self::assertSame($doc, $property->getDocumentation());
    }

    /**
     * @param ReflectionType $reflectionType
     * @param string         $expectedTypeHint
     *
     * @dataProvider makeForParameterWithBuiltInTypeDataProvider
     */
    public function testMakeForParameterWithBuiltInType(
        ?ReflectionType $reflectionType,
        string $expectedTypeHint
    ): void {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionParameter = Mockery::mock(ReflectionParameter::class);
        $doc = Mockery::mock(TestDocumentation::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'getName' => 'App\\Foo',
        ]);

        $reflectionMethod->shouldReceive([
            'getDocComment' => '',
        ]);

        $reflectionParameter->shouldReceive([
            'getName'              => 'bar',
            'getType'              => $reflectionType,
            'getDeclaringFunction' => $reflectionMethod,
        ]);

        $this->config->shouldReceive('testClassTypedProperties')
            ->andReturnFalse();

        $this->typeFactory->shouldReceive('makeFromString')
            ->with($class, $expectedTypeHint, true)
            ->andReturn($expectedTypeHint);

        $this->documentationFactory->shouldReceive('makeForProperty')
            ->with(
                Mockery::type(TestProperty::class),
                Mockery::on(fn (Collection $types) => $types->toArray() === [$expectedTypeHint])
            )
            ->andReturn($doc);

        $property = $this->propertyFactory->makeForParameter($class, $reflectionParameter);

        self::assertSame('bar', $property->getName());
        self::assertSame($doc, $property->getDocumentation());
    }

    public function makeForParameterWithBuiltInTypeDataProvider(): array
    {
        $intType = PhpVersionDependents::makeReflectionTypeMock();

        $intType->shouldReceive([
            '__toString' => 'int',
            'isBuiltIn'  => true,
            'allowsNull' => false,
        ]);

        return [
            [null, 'mixed'],
            [$intType, 'int'],
        ];
    }
}
