<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestTrait;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestTraitTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestTrait
 */
class TestTraitTest extends TestCase
{
    /**
     * @var TestTrait
     */
    protected $trait;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->trait = new TestTrait('Bar');
    }

    public function testItConstructs(): void
    {
        self::assertSame('Bar', $this->trait->getName());
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
