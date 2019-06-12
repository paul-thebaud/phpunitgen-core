<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Parsers;

use Mockery;
use PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestMethod;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionType;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class MockeryMockGeneratorTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator
 */
class MockeryMockGeneratorTest extends TestCase
{
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

        $this->mockeryMockGenerator = new MockeryMockGenerator();
    }

    public function testItGeneratePropertyWhenNoType(): void
    {
        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');

        $parameter = Mockery::mock(ReflectionParameter::class);

        $parameter->shouldReceive('getType')
            ->once()
            ->withNoArgs()
            ->andReturnNull();

        $this->mockeryMockGenerator->generateProperty($class, $parameter);

        $this->assertEmpty($class->getImports());
    }

    public function testItGeneratePropertyWhenBuiltInType(): void
    {
        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');

        $parameter = Mockery::mock(ReflectionParameter::class);
        $type = Mockery::mock(ReflectionType::class);

        $parameter->shouldReceive('getType')
            ->once()
            ->withNoArgs()
            ->andReturn($type);

        $type->shouldReceive('isBuiltin')
            ->once()
            ->withNoArgs()
            ->andReturnTrue();

        $this->mockeryMockGenerator->generateProperty($class, $parameter);

        $this->assertEmpty($class->getImports());
    }

    public function testItGeneratePropertyWhenNotBuiltInType(): void
    {
        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');

        $parameter = Mockery::mock(ReflectionParameter::class);
        $type = Mockery::mock(ReflectionType::class);

        $parameter->shouldReceive('getType')
            ->once()
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

        $this->mockeryMockGenerator->generateProperty($class, $parameter);
        $property = $class->getProperties()->first();

        $this->assertSame($class, $property->getTestClass());
        $this->assertSame('barMock', $property->getName());
        $this->assertSame('Mock', $property->getClass());
        $this->assertTrue(
            $class->getImports()->contains(function (TestImport $import) {
                return $import->getName() === 'Mockery\\Mock'
                    && $import->getAlias() === null;
            })
        );
    }

    public function testItGenerateStatementWhenNoType(): void
    {
        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $method = new TestMethod($class, 'setUp', 'protected');

        $parameter = Mockery::mock(ReflectionParameter::class);

        $parameter->shouldReceive('getType')
            ->once()
            ->withNoArgs()
            ->andReturnNull();

        $this->mockeryMockGenerator->generateStatement($method, $parameter);

        $this->assertEmpty($class->getImports());
        $this->assertEmpty($method->getStatements());
    }

    public function testItGenerateStatementWhenBuiltInType(): void
    {
        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $method = new TestMethod($class, 'setUp', 'protected');

        $parameter = Mockery::mock(ReflectionParameter::class);
        $type = Mockery::mock(ReflectionType::class);

        $parameter->shouldReceive('getType')
            ->once()
            ->withNoArgs()
            ->andReturn($type);

        $type->shouldReceive('isBuiltin')
            ->once()
            ->withNoArgs()
            ->andReturnTrue();

        $this->mockeryMockGenerator->generateStatement($method, $parameter);

        $this->assertEmpty($class->getImports());
        $this->assertEmpty($method->getStatements());
    }

    public function testItGenerateStatementWhenNotBuiltInType(): void
    {
        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $method = new TestMethod($class, 'setUp', 'protected');

        $parameter = Mockery::mock(ReflectionParameter::class);
        $type = Mockery::mock(ReflectionType::class);

        $parameter->shouldReceive('getType')
            ->once()
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
            ->once()
            ->withNoArgs()
            ->andReturn('Bar');

        $this->mockeryMockGenerator->generateStatement($method, $parameter);
        $statement = $method->getStatements()->first();

        $this->assertSame($method, $statement->getTestMethod());
        $this->assertSame(
            '$this->barMock = Mockery::mock(Bar::class);',
            $statement->getStatement()
        );
        $this->assertTrue(
            $class->getImports()->contains(function (TestImport $import) {
                return $import->getName() === 'Bar'
                    && $import->getAlias() === null;
            })
        );
        $this->assertTrue(
            $class->getImports()->contains(function (TestImport $import) {
                return $import->getName() === 'Mockery'
                    && $import->getAlias() === null;
            })
        );
    }
}
