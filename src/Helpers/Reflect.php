<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Helpers;

use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Tightenco\Collect\Support\Collection;

/**
 * Class Reflect.
 *
 * Helper methods for reflection.
 *
 * @internal
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class Reflect
{
    /**
     * Get the immediate methods for the given reflection class.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return ReflectionMethod[]|Collection
     */
    public static function methods(ReflectionClass $reflectionClass): Collection
    {
        return new Collection($reflectionClass->getImmediateMethods());
    }

    /**
     * Get the immediate method matching the given name.
     *
     * @param ReflectionClass $reflectionClass
     * @param string          $name
     *
     * @return ReflectionMethod|null
     */
    public static function method(ReflectionClass $reflectionClass, string $name): ?ReflectionMethod
    {
        return static::methods($reflectionClass)
            ->first(function (ReflectionMethod $reflectionMethod) use ($name) {
                return $reflectionMethod->getShortName() === $name;
            });
    }

    /**
     * Get the properties for the given reflection class.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return ReflectionProperty[]|Collection
     */
    public static function properties(ReflectionClass $reflectionClass): Collection
    {
        return new Collection($reflectionClass->getImmediateProperties());
    }

    /**
     * Get the immediate property matching the given name.
     *
     * @param ReflectionClass $reflectionClass
     * @param string          $name
     *
     * @return ReflectionProperty|null
     */
    public static function property(ReflectionClass $reflectionClass, string $name): ?ReflectionProperty
    {
        return static::properties($reflectionClass)
            ->first(function (ReflectionProperty $reflectionProperty) use ($name) {
                return $reflectionProperty->getName() === $name;
            });
    }

    /**
     * Get the parameters for the given reflection method.
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return ReflectionParameter[]|Collection
     */
    public static function parameters(ReflectionMethod $reflectionMethod): Collection
    {
        return new Collection($reflectionMethod->getParameters());
    }
}
