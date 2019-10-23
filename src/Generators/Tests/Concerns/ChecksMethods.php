<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Concerns;

use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Trait ChecksMethods.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait ChecksMethods
{
    /**
     * Check if the given method corresponds to the given criteria.
     *
     * @param ReflectionMethod $reflectionMethod
     * @param string           $name
     * @param bool             $static
     *
     * @return bool
     */
    protected function isMethod(ReflectionMethod $reflectionMethod, string $name, bool $static = false): bool
    {
        return $reflectionMethod->isStatic() === $static
            && $reflectionMethod->getShortName() === $name;
    }
}
