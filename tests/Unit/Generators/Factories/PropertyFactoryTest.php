<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Factories;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
use PhpUnitGen\Core\Generators\Factories\PropertyFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestProperty;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionType;
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
     * @var DocumentationFactory|Mock
     */
    protected $documentationFactory;

    /**
     * @var ImportFactory|Mock
     */
    protected $importFactory;

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

        $this->documentationFactory = Mockery::mock(DocumentationFactory::class);
        $this->importFactory = Mockery::mock(ImportFactory::class);
        $this->mockGenerator = Mockery::mock(MockGenerator::class);
        $this->propertyFactory = new PropertyFactory();
        $this->propertyFactory->setDocumentationFactory($this->documentationFactory);
        $this->propertyFactory->setImportFactory($this->importFactory);
        $this->propertyFactory->setMockGenerator($this->mockGenerator);
    }

    public function testMakeForClass(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $import = Mockery::mock(TestImport::class);
        $doc = Mockery::mock(TestDocumentation::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'getShortName' => 'Foo',
            'getName'      => 'App\\Foo',
        ]);

        $this->importFactory->shouldReceive('make')
            ->with($class, 'App\\Foo')
            ->andReturn($import);

        $this->documentationFactory->shouldReceive('makeForProperty')
            ->with(Mockery::type(TestProperty::class), $import)
            ->andReturn($doc);

        $property = $this->propertyFactory->makeForClass($class);

        $this->assertSame('foo', $property->getName());
        $this->assertSame($doc, $property->getDocumentation());
    }

    /**
     * @param ReflectionType $reflectionType
     * @param TestImport     $expectedTypeHint
     *
     * @dataProvider makeForParameterWithObjectTypeDataProvider
     */
    public function testMakeForParameterWithObjectType(
        ReflectionType $reflectionType,
        TestImport $expectedTypeHint
    ): void {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionParameter = Mockery::mock(ReflectionParameter::class);
        $doc = Mockery::mock(TestDocumentation::class);
        $mockImport = Mockery::mock(TestImport::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'getName' => 'App\\Foo',
        ]);

        $reflectionParameter->shouldReceive([
            'getName' => 'bar',
            'getType' => $reflectionType,
        ]);

        $this->importFactory->shouldReceive('make')
            ->with($class, $expectedTypeHint->getName())
            ->andReturn($expectedTypeHint);

        $this->mockGenerator->shouldReceive('getMockType')
            ->with($class)
            ->andReturn($mockImport);

        $this->documentationFactory->shouldReceive('makeForProperty')
            ->with(
                Mockery::type(TestProperty::class),
                Mockery::on(function (Collection $typeHints) use ($expectedTypeHint, $mockImport) {
                    return $typeHints->toArray() === [$expectedTypeHint, $mockImport];
                })
            )
            ->andReturn($doc);

        $property = $this->propertyFactory->makeForParameter($class, $reflectionParameter);

        $this->assertSame('bar', $property->getName());
        $this->assertSame($doc, $property->getDocumentation());
    }

    public function makeForParameterWithObjectTypeDataProvider(): array
    {
        $parentType = Mockery::mock(ReflectionType::class);
        $selfType = Mockery::mock(ReflectionType::class);
        $barType = Mockery::mock(ReflectionType::class);

        $parentType->shouldReceive(['__toString' => 'parent']);
        $selfType->shouldReceive(['__toString' => 'self']);
        $barType->shouldReceive([
            '__toString' => 'App\\Bar',
            'isBuiltIn'  => false,
        ]);

        return [
            [$parentType, new TestImport('App\\Foo')],
            [$selfType, new TestImport('App\\Foo')],
            [$barType, new TestImport('App\\Bar')],
        ];
    }

    /**
     * @param ReflectionType $reflectionType
     * @param string     $expectedTypeHint
     *
     * @dataProvider makeForParameterWithBuiltInTypeDataProvider
     */
    public function testMakeForParameterWithBuiltInType(
        ?ReflectionType $reflectionType,
        string $expectedTypeHint
    ): void {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionParameter = Mockery::mock(ReflectionParameter::class);
        $doc = Mockery::mock(TestDocumentation::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'getName' => 'App\\Foo',
        ]);

        $reflectionParameter->shouldReceive([
            'getName' => 'bar',
            'getType' => $reflectionType,
        ]);

        $this->documentationFactory->shouldReceive('makeForProperty')
            ->with(
                Mockery::type(TestProperty::class),
                $expectedTypeHint
            )
            ->andReturn($doc);

        $property = $this->propertyFactory->makeForParameter($class, $reflectionParameter);

        $this->assertSame('bar', $property->getName());
        $this->assertSame($doc, $property->getDocumentation());
    }

    public function makeForParameterWithBuiltInTypeDataProvider(): array
    {
        $intType = Mockery::mock(ReflectionType::class);

        $intType->shouldReceive([
            '__toString' => 'int',
            'isBuiltIn'  => true,
        ]);

        return [
            [null, 'mixed'],
            [$intType, 'int'],
        ];
    }
}
