<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Renderers;

use PhpUnitGen\Core\Renderers\RenderedString;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class RenderedStringTest.
 *
 * @covers \PhpUnitGen\Core\Renderers\RenderedString
 */
class RenderedStringTest extends TestCase
{
    public function testToString(): void
    {
        $line = new RenderedString('class FooTest{}');

        self::assertSame('class FooTest{}', $line->toString());
    }
}
