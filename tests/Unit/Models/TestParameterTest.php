<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestParameter;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestParameterTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestParameter
 */
class TestParameterTest extends TestCase
{
    /**
     * @var TestMethod
     */
    protected $method;

    /**
     * @var TestParameter
     */
    protected $parameter;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $this->method = new TestMethod($class, 'testFoo');
        $this->parameter = new TestParameter($this->method, 'expected', 'string');
    }

    public function testItConstructs(): void
    {
        $this->assertSame($this->method, $this->parameter->getTestMethod());
        $this->assertTrue($this->method->getParameters()->contains($this->parameter));
        $this->assertSame('expected', $this->parameter->getName());
        $this->assertSame('string', $this->parameter->getType());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestParameter')
            ->once()
            ->with($this->parameter);

        $this->parameter->accept($renderer);
    }
}
