<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Parsers\Sources;

use Tests\PhpUnitGen\Core\TestCase;
use PhpUnitGen\Core\Parsers\Sources\StringSource;

/**
 * Class StringSourceTest.
 *
 * @covers \PhpUnitGen\Core\Parsers\Sources\StringSource
 */
class StringSourceTest extends TestCase
{
    public function testItRetrievesSourceCode(): void
    {
        $source = new StringSource('<?php class Foo {}');

        $this->assertSame('<?php class Foo {}', $source->toString());
    }
}
