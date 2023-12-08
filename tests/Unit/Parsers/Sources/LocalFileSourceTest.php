<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Parsers\Sources;

use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Parsers\Sources\LocalFileSource;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class LocalFileSourceTest.
 *
 * @covers \PhpUnitGen\Core\Parsers\Sources\LocalFileSource
 */
class LocalFileSourceTest extends TestCase
{
    public function testItThrowsExceptionIfFileDoesNotExists(): void
    {
        $path = __DIR__.'/this-file-does-not-exists.php';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("the file at {$path} does not exists");

        new LocalFileSource($path);
    }

    public function testItThrowsExceptionIfFileIsADirectory(): void
    {
        $path = __DIR__;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("the file at {$path} does not exists");

        new LocalFileSource($path);
    }

    public function testItRetrievesSourceCode(): void
    {
        $path = __FILE__;

        $source = new LocalFileSource($path);

        self::assertSame(file_get_contents($path), $source->toString());
    }
}
