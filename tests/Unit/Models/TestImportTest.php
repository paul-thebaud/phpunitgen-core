<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestImportTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestImport
 */
class TestImportTest extends TestCase
{
    /**
     * @var TestClass $class
     */
    protected $class;

    /**
     * @var TestImport $import
     */
    protected $import;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->class  = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $this->import = new TestImport($this->class, 'Bar', 'BarAlias');
    }

    public function testItConstructs(): void
    {
        $this->assertSame($this->class, $this->import->getTestClass());
        $this->assertTrue($this->class->getImports()->contains($this->import));
        $this->assertSame('Bar', $this->import->getName());
        $this->assertSame('BarAlias', $this->import->getAlias());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestImport')
            ->once()
            ->with($this->import);

        $this->import->accept($renderer);
    }
}
