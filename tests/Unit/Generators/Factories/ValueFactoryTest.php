<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Factories;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
use PhpUnitGen\Core\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Reflection\ReflectionType;
use Roave\BetterReflection\Reflection\ReflectionClass;
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
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->reflectionType = Mockery::mock(ReflectionType::class);
        $this->class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $this->mockGenerator = Mockery::mock(MockGenerator::class);

        $this->valueFactory = new ValueFactory();
        $this->valueFactory->setMockGenerator($this->mockGenerator);
    }

    public function testWithoutType(): void
    {
        self::assertSame('null', $this->valueFactory->make($this->class, null));
    }

    public function testWithNotBuiltIn(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnFalse();
        $this->reflectionType->shouldReceive('getType')->andReturn('Foo');

        $this->mockGenerator->shouldReceive('generateMock')
            ->with($this->class, 'Foo')
            ->andReturn('Mockery::mock(Foo::class)');

        self::assertSame(
            'Mockery::mock(Foo::class)',
            $this->valueFactory->make($this->class, $this->reflectionType)
        );
    }

    public function testWithBuiltInInteger(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('getType')->andReturn('int');

        self::assertSame('42', $this->valueFactory->make($this->class, $this->reflectionType));
    }

    public function testWithBuiltInFloat(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('getType')->andReturn('float');

        self::assertSame('42.42', $this->valueFactory->make($this->class, $this->reflectionType));
    }

    public function testWithBuiltInString(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('getType')->andReturn('string');

        self::assertSame('\'42\'', $this->valueFactory->make($this->class, $this->reflectionType));
    }

    public function testWithBuiltInBoolean(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('getType')->andReturn('bool');

        self::assertSame('true', $this->valueFactory->make($this->class, $this->reflectionType));
    }

    public function testWithBuiltInCallable(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('getType')->andReturn('callable');

        self::assertSame('function () {}', $this->valueFactory->make($this->class, $this->reflectionType));
    }

    public function testWithBuiltInArray(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('getType')->andReturn('array');

        self::assertSame('[]', $this->valueFactory->make($this->class, $this->reflectionType));
    }

    public function testWithBuiltInIterable(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('getType')->andReturn('iterable');

        self::assertSame('[]', $this->valueFactory->make($this->class, $this->reflectionType));
    }

    public function testWithBuiltInObject(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('getType')->andReturn('object');

        self::assertSame('new \\stdClass()', $this->valueFactory->make($this->class, $this->reflectionType));
    }

    public function testWithBuiltInSelf(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('getType')->andReturn('self');

        $this->class->getReflectionClass()
            ->shouldReceive('getShortName')
            ->andReturn('Foo');
        $this->mockGenerator->shouldReceive('generateMock')
            ->with($this->class, 'Foo')
            ->andReturn('Mockery::mock(Foo::class)');

        self::assertSame(
            'Mockery::mock(Foo::class)',
            $this->valueFactory->make($this->class, $this->reflectionType)
        );
    }

    public function testWithBuiltInParent(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('getType')->andReturn('parent');

        $this->class->getReflectionClass()
            ->shouldReceive('getShortName')
            ->andReturn('Foo');
        $this->mockGenerator->shouldReceive('generateMock')
            ->with($this->class, 'Foo')
            ->andReturn('Mockery::mock(Foo::class)');

        self::assertSame(
            'Mockery::mock(Foo::class)',
            $this->valueFactory->make($this->class, $this->reflectionType)
        );
    }

    public function testWithBuiltInVoid(): void
    {
        $this->reflectionType->shouldReceive('isBuiltIn')->andReturnTrue();
        $this->reflectionType->shouldReceive('getType')->andReturn('void');

        self::assertSame('null', $this->valueFactory->make($this->class, $this->reflectionType));
    }
}
