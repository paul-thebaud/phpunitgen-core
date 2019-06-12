<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestParameter;
use PhpUnitGen\Core\Models\TestProvider;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestMethodTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestMethod
 */
class TestMethodTest extends TestCase
{
    /**
     * @var TestClass
     */
    protected $class;

    /**
     * @var TestMethod
     */
    protected $method;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $this->method = new TestMethod($this->class, 'testFoo', 'protected');
    }

    public function testItConstructs(): void
    {
        $this->assertSame($this->class, $this->method->getTestClass());
        $this->assertTrue($this->class->getMethods()->contains($this->method));
        $this->assertSame('testFoo', $this->method->getName());
        $this->assertSame('protected', $this->method->getVisibility());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestMethod')
            ->once()
            ->with($this->method);

        $this->method->accept($renderer);
    }

    public function testItDefinesProvider(): void
    {
        $provider = Mockery::mock(TestProvider::class);

        $this->method->setProvider($provider);

        $this->assertSame($provider, $this->method->getProvider());
    }

    public function testItAddsParameter(): void
    {
        $parameter = Mockery::mock(TestParameter::class);

        $this->assertFalse($this->method->getParameters()->contains($parameter));

        $this->method->addParameter($parameter);

        $this->assertTrue($this->method->getParameters()->contains($parameter));
    }

    public function testItAddsStatement(): void
    {
        $statement = Mockery::mock(TestStatement::class);

        $this->assertFalse($this->method->getStatements()->contains($statement));

        $this->method->addStatement($statement);

        $this->assertTrue($this->method->getStatements()->contains($statement));
    }
}
