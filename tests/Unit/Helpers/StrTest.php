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
        self::assertSame($expected, Str::beforeLast($search, $subject));
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
     * @dataProvider afterFirstProvider
     */
    public function testAfterFirst(string $expected, string $search, string $subject): void
    {
        self::assertSame($expected, Str::afterFirst($search, $subject));
    }

    public function afterFirstProvider(): array
    {
        return [
            ['Foo', '\\', 'Foo'],
            ['Bar', '\\', 'Foo\\Bar'],
            ['Bar\\Baz', '\\', 'Foo\\Bar\\Baz'],
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
        self::assertSame($expected, Str::afterLast($search, $subject));
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
        self::assertSame($expected, Str::replaceFirst($search, $replace, $subject));
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
        self::assertSame($expected, Str::replaceLast($search, $replace, $subject));
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
     * @param bool            $expected
     * @param string|string[] $search
     * @param string          $subject
     *
     * @dataProvider containsProvider
     */
    public function testContains(bool $expected, $search, string $subject): void
    {
        self::assertSame($expected, Str::contains($search, $subject));
    }

    public function containsProvider(): array
    {
        return [
            [false, 'Foo', 'Bar Baz'],
            [true, 'Foo', 'Foo Bar Baz'],
            [true, 'Foo', 'Bar Baz Foo'],
            [false, ['Foo'], 'Bar Baz'],
            [false, ['Foo', 'FooBar'], 'Bar Baz'],
            [true, ['Foo'], 'Foo Bar Baz'],
            [true, ['FooBar', 'FooBaz', 'Baz'], 'Bar Baz Foo'],
        ];
    }

    /**
     * @param bool            $expected
     * @param string|string[] $expression
     * @param string          $subject
     *
     * @dataProvider containsRegexProvider
     */
    public function testContainsRegex(bool $expected, $expression, string $subject): void
    {
        self::assertSame($expected, Str::containsRegex($expression, $subject));
    }

    public function containsRegexProvider(): array
    {
        return [
            [false, 'foo', 'Bar Baz'],
            [true, 'foo', 'Foo Bar Baz'],
            [true, 'foo', 'Bar Baz Foo'],
            [false, ['foo'], 'Bar Baz'],
            [false, ['foo', 'FooBar'], 'Bar Baz'],
            [true, ['foo'], 'Foo Bar Baz'],
            [true, ['foobar', '^[barz ]+foo$', 'Baz'], 'Bar Baz Foo'],
            [true, ['foobar', '^.*$', 'Baz'], 'Bar Baz Foo'],
        ];
    }

    /**
     * @param bool   $expected
     * @param string $search
     * @param string $subject
     *
     * @dataProvider startsWithProvider
     */
    public function testStartsWith(bool $expected, $search, string $subject): void
    {
        self::assertSame($expected, Str::startsWith($search, $subject));
    }

    public function startsWithProvider(): array
    {
        return [
            [false, 'Foo', 'Bar Baz'],
            [false, 'Foo', 'Bar Foo Baz'],
            [true, 'Foo', 'Foo Bar Baz'],
            [true, ['Bar', 'Foo'], 'Foo Bar Baz'],
        ];
    }

    /**
     * @param bool   $expected
     * @param string $search
     * @param string $subject
     *
     * @dataProvider endsWithProvider
     */
    public function testEndsWith(bool $expected, $search, string $subject): void
    {
        self::assertSame($expected, Str::endsWith($search, $subject));
    }

    public function endsWithProvider(): array
    {
        return [
            [false, 'Foo', 'Bar Baz'],
            [false, 'Foo', 'Bar Foo Baz'],
            [true, 'Baz', 'Foo Bar Baz'],
            [true, ['Bar', 'Baz'], 'Foo Bar Baz'],
        ];
    }
}
