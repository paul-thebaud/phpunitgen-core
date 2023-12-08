<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestImport;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestImportTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestImport
 */
class TestImportTest extends TestCase
{
    /**
     * @var TestImport
     */
    protected $import;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->import = new TestImport('Bar', 'BarAlias');
    }

    public function testItConstructs(): void
    {
        self::assertSame('Bar', $this->import->getName());
        self::assertSame('BarAlias', $this->import->getAlias());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestImport')
            ->once()
            ->with($this->import);

        $this->import->accept($renderer);
    }

    public function testItReturnsAliasWhenDefined(): void
    {
        self::assertSame('BarAlias', $this->import->getFinalName());
    }

    public function testItReturnsShortNameWhenAliasNotDefined(): void
    {
        $import = new TestImport('Foo\\Bar\\Baz');

        self::assertSame('Baz', $import->getFinalName());
    }
}
