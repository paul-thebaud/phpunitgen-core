<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestParameter;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestParameterTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestParameter
 * @covers \PhpUnitGen\Core\Models\Concerns\HasType
 */
class TestParameterTest extends TestCase
{
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

        $this->parameter = new TestParameter('expected');
    }

    public function testItConstructs(): void
    {
        $this->assertSame('expected', $this->parameter->getName());
    }

    public function testItHasType(): void
    {
        $this->assertSame(null, $this->parameter->getType());
        $this->parameter->setType('int');
        $this->assertSame('int', $this->parameter->getType());
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
