<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use PhpUnitGen\Core\Models\TestTrait;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestClassTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestClass
 * @covers \PhpUnitGen\Core\Models\Concerns\HasTestClassParent
 */
class TestClassTest extends TestCase
{
    /**
     * @var Mockery\Mock
     */
    protected $reflectionClass;

    /**
     * @var TestClass
     */
    protected $class;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->reflectionClass = Mockery::mock(ReflectionClass::class);
        $this->class = new TestClass($this->reflectionClass, 'Bar\\FooTest');
    }

    public function testItConstructs(): void
    {
        self::assertSame($this->reflectionClass, $this->class->getReflectionClass());
        self::assertSame('Bar\\FooTest', $this->class->getName());
        self::assertSame('Bar', $this->class->getNamespace());
        self::assertSame('FooTest', $this->class->getShortName());

        self::assertEmpty($this->class->getImports());
        self::assertEmpty($this->class->getTraits());
        self::assertEmpty($this->class->getProperties());
        self::assertEmpty($this->class->getMethods());
    }

    public function testItGetNamesWhenNoNamespace(): void
    {
        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');

        self::assertNull($class->getNamespace());
        self::assertSame('FooTest', $class->getShortName());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestClass')
            ->once()
            ->with($this->class);

        $this->class->accept($renderer);
    }

    public function testItAddsImport(): void
    {
        $import = new TestImport('Foo');

        $this->class->addImport($import);

        self::assertSame($this->class, $import->getTestClass());
    }

    public function testItAddsTrait(): void
    {
        $trait = new TestTrait('Foo');

        $this->class->addTrait($trait);

        self::assertSame($this->class, $trait->getTestClass());
    }

    public function testItAddsProperty(): void
    {
        $property = new TestProperty('foo', 'Foo');

        $this->class->addProperty($property);

        self::assertSame($this->class, $property->getTestClass());
    }

    public function testItAddsMethod(): void
    {
        $method = new TestMethod('testFoo');

        $this->class->addMethod($method);

        self::assertSame($this->class, $method->getTestClass());
    }

    public function testItHasDocumentation(): void
    {
        $documentation = new TestDocumentation();

        $this->class->setDocumentation($documentation);

        self::assertSame($documentation, $this->class->getDocumentation());
    }
}
