<?php

namespace Tests\PhpUnitGen\Core\Helpers;

use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\Mock;
use PhpUnitGen\Core\Helpers\Str;
use Roave\BetterReflection\Reflection\ReflectionNamedType;
use Roave\BetterReflection\Reflection\ReflectionType;

/**
 * Class PhpVersionDependents.
 *
 * This class contains helpers to tests classes depending on PHP version.
 *
 * @author Paul Thébaud <paul.thebaud29@gmail.com>
 */
class PhpVersionDependents
{
    /**
     * Checks if PHP is version 8 or not.
     *
     * @return bool
     */
    public static function isPhp8(): bool
    {
        return Str::startsWith('8.', phpversion());
    }

    /**
     * Get a reflection type mock.
     *
     * @return ReflectionType|Mock|LegacyMockInterface
     */
    public static function makeReflectionTypeMock()
    {
        if (self::isPhp8()) {
            return Mockery::mock(ReflectionNamedType::class);
        }

        return Mockery::mock(ReflectionType::class);
    }
}
