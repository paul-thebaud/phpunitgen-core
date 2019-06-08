<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestTrait;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestTraitTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestTrait
 */
class TestTraitTest extends TestCase
{
    /**
     * @var TestClass $class
     */
    protected $class;

    /**
     * @var TestTrait $trait
     */
    protected $trait;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $this->trait = new TestTrait($this->class, 'Bar');
    }

    public function testItConstructs(): void
    {
        $this->assertSame($this->class, $this->trait->getTestClass());
        $this->assertTrue($this->class->getTraits()->contains($this->trait));
        $this->assertSame('Bar', $this->trait->getName());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestTrait')
            ->once()
            ->with($this->trait);

        $this->trait->accept($renderer);
    }
}
