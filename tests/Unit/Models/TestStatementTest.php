<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestStatement;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestStatementTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestStatement
 */
class TestStatementTest extends TestCase
{
    /**
     * @var TestStatement
     */
    protected $statement;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->statement = new TestStatement('/** @todo */');
    }

    public function testItConstructs(): void
    {
        $this->assertSame('/** @todo */', $this->statement->getStatement());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestStatement')
            ->once()
            ->with($this->statement);

        $this->statement->accept($renderer);
    }
}
