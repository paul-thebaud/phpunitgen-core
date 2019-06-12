<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Helpers;

use PhpUnitGen\Core\Helpers\Str;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class StrTest.
 *
 * @covers \PhpUnitGen\Core\Helpers\Str
 */
class StrTest extends TestCase
{
    /**
     * @param string $expected
     * @param string $search
     * @param string $subject
     *
     * @dataProvider beforeLastProvider
     */
    public function testBeforeLast(string $expected, string $search, string $subject): void
    {
        $this->assertSame($expected, Str::beforeLast($search, $subject));
    }

    public function beforeLastProvider(): array
    {
        return [
            ['Foo', '\\', 'Foo'],
            ['Foo', '\\', 'Foo\\Bar'],
            ['Foo\\Bar', '\\', 'Foo\\Bar\\Baz'],
        ];
    }

    /**
     * @param string $expected
     * @param string $search
     * @param string $subject
     *
     * @dataProvider afterLastProvider
     */
    public function testAfterLast(string $expected, string $search, string $subject): void
    {
        $this->assertSame($expected, Str::afterLast($search, $subject));
    }

    public function afterLastProvider(): array
    {
        return [
            ['Foo', '\\', 'Foo'],
            ['Bar', '\\', 'Foo\\Bar'],
            ['Baz', '\\', 'Foo\\Bar\\Baz'],
        ];
    }

    /**
     * @param string $expected
     * @param string $search
     * @param string $replace
     * @param string $subject
     *
     * @dataProvider replaceFirstProvider
     */
    public function testReplaceFirst(string $expected, string $search, string $replace, string $subject): void
    {
        $this->assertSame($expected, Str::replaceFirst($search, $replace, $subject));
    }

    public function replaceFirstProvider(): array
    {
        return [
            ['Foo Bar', 'Baz', 'Foo', 'Foo Bar'],
            ['Foo Foo Bar Baz', 'Baz', 'Foo', 'Foo Baz Bar Baz'],
            ['Foo', 'Foo / Bar', 'Foo', 'Foo / Bar'],
        ];
    }

    /**
     * @param string $expected
     * @param string $search
     * @param string $replace
     * @param string $subject
     *
     * @dataProvider replaceLastProvider
     */
    public function testReplaceLast(string $expected, string $search, string $replace, string $subject): void
    {
        $this->assertSame($expected, Str::replaceLast($search, $replace, $subject));
    }

    public function replaceLastProvider(): array
    {
        return [
            ['Foo Bar', 'Baz', 'Foo', 'Foo Bar'],
            ['Foo Baz Bar Foo', 'Baz', 'Foo', 'Foo Baz Bar Baz'],
            ['Foo', 'Foo / Bar', 'Foo', 'Foo / Bar'],
        ];
    }

    /**
     * @param bool   $expected
     * @param string $search
     * @param string $subject
     *
     * @dataProvider containsProvider
     */
    public function testContains(bool $expected, string $search, string $subject): void
    {
        $this->assertSame($expected, Str::contains($search, $subject));
    }

    public function containsProvider(): array
    {
        return [
            [false, 'Foo', 'Bar Baz'],
            [true, 'Foo', 'Foo Bar Baz'],
            [true, 'Foo', 'Bar Baz Foo'],
        ];
    }

    /**
     * @param bool   $expected
     * @param string $search
     * @param string $subject
     *
     * @dataProvider startsWithProvider
     */
    public function testStartsWith(bool $expected, string $search, string $subject): void
    {
        $this->assertSame($expected, Str::startsWith($search, $subject));
    }

    public function startsWithProvider(): array
    {
        return [
            [false, 'Foo', 'Bar Baz'],
            [false, 'Foo', 'Bar Foo Baz'],
            [true, 'Foo', 'Foo Bar Baz'],
        ];
    }
}
