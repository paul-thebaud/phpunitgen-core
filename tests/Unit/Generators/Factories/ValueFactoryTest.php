<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Factories;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
use PhpUnitGen\Core\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionType;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class ValueFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Factories\ValueFactory
 */
class ValueFactoryTest extends TestCase
{
    /**
     * @var ReflectionType|Mock
     */
    protected $reflectionType;

    /**
     * @var TestClass|Mock
     */
    protected $class;

    /**
     * @var MockGenerator|Mock
     */
    protected $mockGenerator;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->reflectionType = Mockery::mock(ReflectionType::class);
        $this->class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $this->mockGenerator = Mockery::mock(MockGenerator::class);

        $this->valueFactory = new ValueFactory($this->mockGenerator);
    }

    public function testWithoutType(): void
    {
        $this->assertSame('null', $this->valueFactory->create($this->class, null));
    }

    public function testWithNotBuiltIn(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnFalse();
        $this->reflectionType->shouldReceive('__toString')->andReturn('Foo');

        $this->mockGenerator->shouldReceive('generateMock')
            ->with($this->class, 'Foo')
            ->andReturn('Mockery::mock(Foo::class)');

        $this->assertSame(
            'Mockery::mock(Foo::class)',
            $this->valueFactory->create($this->class, $this->reflectionType)
        );
    }

    public function testWithBuiltInInteger(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('__toString')->andReturn('int');

        $this->assertSame('42', $this->valueFactory->create($this->class, $this->reflectionType));
    }

    public function testWithBuiltInFloat(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('__toString')->andReturn('float');

        $this->assertSame('42.42', $this->valueFactory->create($this->class, $this->reflectionType));
    }

    public function testWithBuiltInString(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('__toString')->andReturn('string');

        $this->assertSame('\'42\'', $this->valueFactory->create($this->class, $this->reflectionType));
    }

    public function testWithBuiltInBoolean(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('__toString')->andReturn('bool');

        $this->assertSame('true', $this->valueFactory->create($this->class, $this->reflectionType));
    }

    public function testWithBuiltInCallable(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('__toString')->andReturn('callable');

        $this->assertSame('function () {}', $this->valueFactory->create($this->class, $this->reflectionType));
    }

    public function testWithBuiltInArray(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('__toString')->andReturn('array');

        $this->assertSame('[]', $this->valueFactory->create($this->class, $this->reflectionType));
    }

    public function testWithBuiltInIterable(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('__toString')->andReturn('iterable');

        $this->assertSame('[]', $this->valueFactory->create($this->class, $this->reflectionType));
    }

    public function testWithBuiltInObject(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('__toString')->andReturn('object');

        $this->assertSame('new \\stdClass()', $this->valueFactory->create($this->class, $this->reflectionType));
    }

    public function testWithBuiltInSelf(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('__toString')->andReturn('self');

        $this->class->getReflectionClass()
            ->shouldReceive('getShortName')
            ->andReturn('Foo');
        $this->mockGenerator->shouldReceive('generateMock')
            ->with($this->class, 'Foo')
            ->andReturn('Mockery::mock(Foo::class)');

        $this->assertSame(
            'Mockery::mock(Foo::class)',
            $this->valueFactory->create($this->class, $this->reflectionType)
        );
    }

    public function testWithBuiltInParent(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('__toString')->andReturn('parent');

        $this->class->getReflectionClass()
            ->shouldReceive('getShortName')
            ->andReturn('Foo');
        $this->mockGenerator->shouldReceive('generateMock')
            ->with($this->class, 'Foo')
            ->andReturn('Mockery::mock(Foo::class)');

        $this->assertSame(
            'Mockery::mock(Foo::class)',
            $this->valueFactory->create($this->class, $this->reflectionType)
        );
    }

    public function testWithBuiltInVoid(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('__toString')->andReturn('void');

        $this->assertSame('null', $this->valueFactory->create($this->class, $this->reflectionType));
    }
}
