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

        $this->parameter = new TestParameter('expected', 'string');
    }

    public function testItConstructs(): void
    {
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
