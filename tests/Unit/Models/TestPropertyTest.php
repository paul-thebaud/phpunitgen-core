<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestProperty;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestPropertyTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestProperty
 */
class TestPropertyTest extends TestCase
{
    /**
     * @var TestClass
     */
    protected $class;

    /**
     * @var TestProperty
     */
    protected $property;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $this->property = new TestProperty($this->class, 'foo', 'Foo', true);
    }

    public function testItConstructs(): void
    {
        $this->assertSame($this->class, $this->property->getTestClass());
        $this->assertTrue($this->class->getProperties()->contains($this->property));
        $this->assertSame('foo', $this->property->getName());
        $this->assertSame('Foo', $this->property->getClass());
        $this->assertTrue($this->property->isTestedClass());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestProperty')
            ->once()
            ->with($this->property);

        $this->property->accept($renderer);
    }
}
