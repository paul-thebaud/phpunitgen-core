<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Tests\Unit\Parsers;

use PHPUnit\Framework\TestCase;
use PhpUnitGen\Core\Sources\StringSource;

/**
 * Class StringSourceTest.
 *
 * @covers StringSource
 */
class StringSourceTest extends TestCase
{
    public function testItRetrievesSourceCode(): void
    {
        $source = new StringSource('<?php class Foo {}');

        $this->assertSame('<?php class Foo {}', $source->toString());
    }
}
