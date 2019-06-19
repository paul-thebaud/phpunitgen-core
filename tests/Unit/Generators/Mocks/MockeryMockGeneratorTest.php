<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Parsers;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionType;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class MockeryMockGeneratorTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Mocks\AbstractMockGenerator
 * @covers \PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator
 */
class MockeryMockGeneratorTest extends TestCase
{
    /**
     * @var ImportFactory|Mock
     */
    protected $importFactory;

    /**
     * @var MockeryMockGenerator
     */
    protected $mockeryMockGenerator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importFactory = Mockery::mock(ImportFactory::class);
        $this->mockeryMockGenerator = new MockeryMockGenerator($this->importFactory);
    }

    public function testItDoesNotGenerateForParameterWhenNoType(): void
    {
        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $method = new TestMethod('setUp');
        $method->setTestClass($class);

        $parameter = Mockery::mock(ReflectionParameter::class);

        $parameter->shouldReceive('getType')
            ->once()
            ->withNoArgs()
            ->andReturnNull();

        $this->importFactory->shouldReceive('create')->never();

        $this->mockeryMockGenerator->generateForParameter($method, $parameter);
    }

    public function testItDoesNotGenerateForParameterWhenBuiltInType(): void
    {
        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $method = new TestMethod('setUp');
        $method->setTestClass($class);

        $parameter = Mockery::mock(ReflectionParameter::class);
        $type = Mockery::mock(ReflectionType::class);

        $parameter->shouldReceive('getType')
            ->twice()
            ->withNoArgs()
            ->andReturn($type);

        $type->shouldReceive('isBuiltin')
            ->once()
            ->withNoArgs()
            ->andReturnTrue();

        $this->importFactory->shouldReceive('create')->never();

        $this->mockeryMockGenerator->generateForParameter($method, $parameter);
    }

    public function testItGenerateForParameterWhenNotBuiltInType(): void
    {
        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $method = new TestMethod('setUp');
        $method->setTestClass($class);

        $parameter = Mockery::mock(ReflectionParameter::class);
        $type = Mockery::mock(ReflectionType::class);

        $parameter->shouldReceive('getType')
            ->times(4)
            ->withNoArgs()
            ->andReturn($type);

        $type->shouldReceive('isBuiltin')
            ->once()
            ->withNoArgs()
            ->andReturnFalse();

        $parameter->shouldReceive('getName')
            ->once()
            ->withNoArgs()
            ->andReturn('bar');

        $type->shouldReceive('__toString')
            ->twice()
            ->withNoArgs()
            ->andReturn('Bar');

        $this->importFactory->shouldReceive('create')
            ->once()
            ->with($class, 'Mockery\\Mock')
            ->andReturn(new TestImport('Mockery\\Mock'));
        $this->importFactory->shouldReceive('create')
            ->once()
            ->with($class, 'Mockery')
            ->andReturn(new TestImport('Mockery'));
        $this->importFactory->shouldReceive('create')
            ->once()
            ->with($class, 'Bar')
            ->andReturn(new TestImport('Bar'));

        $this->mockeryMockGenerator->generateForParameter($method, $parameter);

        /** @var TestProperty $property */
        $property = $class->getProperties()->first();

        $this->assertSame($class, $property->getTestClass());
        $this->assertSame('barMock', $property->getName());
        $this->assertCount(1, $property->getDocumentation()->getLines());
        $this->assertSame('@var Mock|Bar', $property->getDocumentation()->getLines()->first());
        $this->assertCount(1, $method->getStatements());
        $this->assertCount(1, $method->getStatements()->first()->getLines());
        $this->assertSame(
            '$this->barMock = Mockery::mock(Bar::class);',
            $method->getStatements()->first()->getLines()->first()
        );
    }
}
