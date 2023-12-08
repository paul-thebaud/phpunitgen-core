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
        self::assertSame(1, $this->statement->getLines()->count());
        self::assertSame('/** @todo */', $this->statement->getLines()->first());

        self::assertSame(0, (new TestStatement())->getLines()->count());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestStatement')
            ->once()
            ->with($this->statement);

        $this->statement->accept($renderer);
    }

    public function testItAddsLine(): void
    {
        self::assertSame(1, $this->statement->getLines()->count());

        $this->statement->addLine('$this->fooBar();');

        self::assertSame(2, $this->statement->getLines()->count());
        self::assertSame('$this->fooBar();', $this->statement->getLines()->last());
    }

    public function testItRemovesLine(): void
    {
        self::assertSame(1, $this->statement->getLines()->count());

        $this->statement->removeLine();

        self::assertSame(0, $this->statement->getLines()->count());
    }

    public function testItPrepends(): void
    {
        $this->statement->prepend('foo bar ');

        self::assertSame('foo bar /** @todo */', $this->statement->getLines()->last());
    }

    public function testItAppends(): void
    {
        $this->statement->append(' foo bar');

        self::assertSame('/** @todo */ foo bar', $this->statement->getLines()->last());
    }

    public function testItPrependsWhenCustomKey(): void
    {
        $this->statement->addLine('new line which wont be updated');

        $this->statement->prepend('foo bar ', 0);

        self::assertSame('foo bar /** @todo */', $this->statement->getLines()->first());
    }

    public function testItAppendsWhenCustomKey(): void
    {
        $this->statement->addLine('new line which wont be updated');

        $this->statement->append(' foo bar', 0);

        self::assertSame('/** @todo */ foo bar', $this->statement->getLines()->first());
    }
}
