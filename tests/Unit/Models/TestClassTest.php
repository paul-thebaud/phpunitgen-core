<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestClass;
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
 */
class TestClassTest extends TestCase
{
    /**
     * @var Mockery\Mock $reflectionClass
     */
    protected $reflectionClass;

    /**
     * @var TestClass $class
     */
    protected $class;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->reflectionClass = Mockery::mock(ReflectionClass::class);
        $this->class           = new TestClass($this->reflectionClass, 'Bar\\FooTest');
    }

    public function testItConstructs(): void
    {
        $this->assertSame($this->reflectionClass, $this->class->getReflectionClass());
        $this->assertSame('Bar\\FooTest', $this->class->getName());
        $this->assertSame('Bar', $this->class->getNamespace());
        $this->assertSame('FooTest', $this->class->getShortName());

        $this->assertEmpty($this->class->getImports());
        $this->assertEmpty($this->class->getTraits());
        $this->assertEmpty($this->class->getProperties());
        $this->assertEmpty($this->class->getMethods());
    }

    public function testItGetNamesWhenNoNamespace(): void
    {
        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');

        $this->assertNull($class->getNamespace());
        $this->assertSame('FooTest', $class->getShortName());
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
        $import = Mockery::mock(TestImport::class);

        $this->assertFalse($this->class->getImports()->contains($import));

        $this->class->addImport($import);

        $this->assertTrue($this->class->getImports()->contains($import));
    }

    public function testItAddsTrait(): void
    {
        $trait = Mockery::mock(TestTrait::class);

        $this->assertFalse($this->class->getTraits()->contains($trait));

        $this->class->addTrait($trait);

        $this->assertTrue($this->class->getTraits()->contains($trait));
    }

    public function testItAddsProperty(): void
    {
        $property = Mockery::mock(TestProperty::class);

        $this->assertFalse($this->class->getProperties()->contains($property));

        $this->class->addProperty($property);

        $this->assertTrue($this->class->getProperties()->contains($property));
    }

    public function testItAddsMethod(): void
    {
        $method = Mockery::mock(TestMethod::class);

        $this->assertFalse($this->class->getMethods()->contains($method));

        $this->class->addMethod($method);

        $this->assertTrue($this->class->getMethods()->contains($method));
    }
}
