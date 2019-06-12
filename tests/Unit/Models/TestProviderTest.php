<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProvider;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestProviderTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestProvider
 */
class TestProviderTest extends TestCase
{
    /**
     * @var TestMethod
     */
    protected $method;

    /**
     * @var TestProvider
     */
    protected $provider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $this->method = new TestMethod($class, 'testFoo');
        $this->provider = new TestProvider($this->method, 'fooProvider', [['expected', 'actual']]);
    }

    public function testItConstructs(): void
    {
        $this->assertSame($this->method, $this->provider->getTestMethod());
        $this->assertSame($this->provider, $this->method->getProvider());
        $this->assertSame('fooProvider', $this->provider->getName());
        $this->assertSame([['expected', 'actual']], $this->provider->getData());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestProvider')
            ->once()
            ->with($this->provider);

        $this->provider->accept($renderer);
    }
}
