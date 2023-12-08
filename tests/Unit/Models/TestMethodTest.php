<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestParameter;
use PhpUnitGen\Core\Models\TestProvider;
use PhpUnitGen\Core\Models\TestStatement;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestMethodTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestMethod
 * @covers \PhpUnitGen\Core\Models\Concerns\HasTestMethodParent
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
        self::assertSame('testFoo', $this->method->getName());
        self::assertSame('protected', $this->method->getVisibility());

        self::assertNull($this->method->getProvider());
        self::assertEmpty($this->method->getParameters());
        self::assertEmpty($this->method->getStatements());
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

        self::assertSame($this->method, $provider->getTestMethod());
    }

    public function testItAddsParameter(): void
    {
        $parameter = new TestParameter('foo');

        $this->method->addParameter($parameter);

        self::assertSame($this->method, $parameter->getTestMethod());
    }

    public function testItAddsStatement(): void
    {
        $statement = new TestStatement('foo');

        $this->method->addStatement($statement);

        self::assertSame($this->method, $statement->getTestMethod());
    }

    public function testItHasDocumentation(): void
    {
        $documentation = new TestDocumentation();

        $this->method->setDocumentation($documentation);

        self::assertSame($documentation, $this->method->getDocumentation());
    }
}
