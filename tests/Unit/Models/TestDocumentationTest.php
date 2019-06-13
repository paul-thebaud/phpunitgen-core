<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestDocumentation;
use Tests\PhpUnitGen\Core\TestCase;
use Tightenco\Collect\Support\Collection;

/**
 * Class TestDocumentationTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestDocumentation
 */
class TestDocumentationTest extends TestCase
{
    /**
     * @var TestDocumentation
     */
    protected $documentation;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->documentation = new TestDocumentation(new Collection(['@covers Foo']));
    }

    public function testItConstructs(): void
    {
        $this->assertSame([
            '@covers Foo',
        ], $this->documentation->getLines()->toArray());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestDocumentation')
            ->once()
            ->with($this->documentation);

        $this->documentation->accept($renderer);
    }

    public function testItAddsLine(): void
    {
        $this->documentation->addLine('@author John Doe');

        $this->assertSame([
            '@covers Foo',
            '@author John Doe',
        ], $this->documentation->getLines()->toArray());
    }
}
