<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestParameter;
use PhpUnitGen\Core\Models\TestProvider;
use PhpUnitGen\Core\Models\TestStatement;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestMethodTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestMethod
 */
class TestMethodTest extends TestCase
{
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

        $this->method = new TestMethod('testFoo', 'protected');
    }

    public function testItConstructs(): void
    {
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
        $provider = new TestProvider('foo', []);

        $this->method->setProvider($provider);

        $this->assertSame($this->method, $provider->getTestMethod());
    }

    public function testItAddsParameter(): void
    {
        $parameter = new TestParameter('foo');

        $this->method->addParameter($parameter);

        $this->assertSame($this->method, $parameter->getTestMethod());
    }

    public function testItAddsStatement(): void
    {
        $statement = new TestStatement('foo');

        $this->method->addStatement($statement);

        $this->assertSame($this->method, $statement->getTestMethod());
    }
}
