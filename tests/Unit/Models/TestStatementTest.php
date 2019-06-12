<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestStatementTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestStatement
 */
class TestStatementTest extends TestCase
{
    /**
     * @var TestMethod
     */
    protected $method;

    /**
     * @var TestStatement
     */
    protected $statement;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $this->method = new TestMethod($class, 'testFoo');
        $this->statement = new TestStatement($this->method, '/** @todo */');
    }

    public function testItConstructs(): void
    {
        $this->assertSame($this->method, $this->statement->getTestMethod());
        $this->assertTrue($this->method->getStatements()->contains($this->statement));
        $this->assertSame('/** @todo */', $this->statement->getStatement());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestStatement')
            ->once()
            ->with($this->statement);

        $this->statement->accept($renderer);
    }
}
