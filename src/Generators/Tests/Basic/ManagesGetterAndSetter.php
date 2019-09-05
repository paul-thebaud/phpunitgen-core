<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Basic;

use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Helpers\Str;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionProperty;

/**
 * Trait ManagesGetterAndSetter.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait ManagesGetterAndSetter
{
    /**
     * Check if the given method is a getter or a setter.
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     */
    protected function isGetterOrSetter(ReflectionMethod $reflectionMethod): bool
    {
        return $this->isGetter($reflectionMethod) || $this->isSetter($reflectionMethod);
    }

    /**
     * Check if the given method is a getter (begins with "get" and has a corresponding property).
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     */
    protected function isGetter(ReflectionMethod $reflectionMethod): bool
    {
        return $this->getPropertyFromMethod($reflectionMethod, 'get') !== null;
    }

    /**
     * Check if the given method is a setter (begins with "set" and has a corresponding property).
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     */
    protected function isSetter(ReflectionMethod $reflectionMethod): bool
    {
        return $this->getPropertyFromMethod($reflectionMethod, 'set') !== null;
    }

    /**
     * Get the property for the given method by removing prefix from the method name.
     *
     * @param ReflectionMethod $reflectionMethod
     * @param string           $prefix
     *
     * @return ReflectionProperty|null
     */
    private function getPropertyFromMethod(ReflectionMethod $reflectionMethod, string $prefix): ?ReflectionProperty
    {
        $methodName = $reflectionMethod->getShortName();

        if (! Str::startsWith($prefix, $methodName)) {
            return null;
        }

        $property = Reflect::property(
            $reflectionMethod->getDeclaringClass(),
            lcfirst(Str::replaceFirst($prefix, '', $methodName))
        );

        if (! $property || $reflectionMethod->isStatic() !== $property->isStatic()) {
            return null;
        }

        return $property;
    }
}
