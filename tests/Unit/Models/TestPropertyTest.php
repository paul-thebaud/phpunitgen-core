<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestProperty;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestPropertyTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestProperty
 */
class TestPropertyTest extends TestCase
{
    /**
     * @var TestProperty
     */
    protected $property;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->property = new TestProperty('foo');
    }

    public function testItConstructs(): void
    {
        self::assertSame('foo', $this->property->getName());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestProperty')
            ->once()
            ->with($this->property);

        $this->property->accept($renderer);
    }

    public function testItHasDocumentation(): void
    {
        $documentation = new TestDocumentation();

        $this->property->setDocumentation($documentation);

        self::assertSame($documentation, $this->property->getDocumentation());
    }
}
