<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Renderers;

use PhpUnitGen\Core\Renderers\RenderedLine;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class RenderedLineTest.
 *
 * @covers \PhpUnitGen\Core\Renderers\RenderedLine
 */
class RenderedLineTest extends TestCase
{
    public function testItPrepends(): void
    {
        $line = new RenderedLine(0, '->bar();');

        $line->prepend('$foo');

        self::assertSame('$foo->bar();', $line->render());
    }

    public function testItAppends(): void
    {
        $line = new RenderedLine(0, '$foo');

        $line->append('->bar();');

        self::assertSame('$foo->bar();', $line->render());
    }

    public function testItRenderWithoutIndentation(): void
    {
        $line = new RenderedLine(0, '$foo->bar();');

        self::assertSame('$foo->bar();', $line->render());
        self::assertSame('$foo->bar();', $line->getContent());
    }

    public function testItRenderWithIndentation(): void
    {
        $line = new RenderedLine(3, '$foo->bar();');

        self::assertSame('            $foo->bar();', $line->render());
        self::assertSame('$foo->bar();', $line->getContent());
    }

    public function testItRenderWithIndentationAndEmptyContent(): void
    {
        $line = new RenderedLine(3, '');

        self::assertSame('', $line->render());
        self::assertSame('', $line->getContent());
    }
}
