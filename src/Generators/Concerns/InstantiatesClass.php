<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Concerns;

use PhpUnitGen\Core\Helpers\Reflect;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Trait InstantiatesClass.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait InstantiatesClass
{
    /**
     * Retrieve the name of the class instance property.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return string
     */
    protected function getPropertyName(ReflectionClass $reflectionClass): string
    {
        return lcfirst($reflectionClass->getShortName());
    }

    /**
     * Retrieve the class constructor only if it is public and non-abstract.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return ReflectionMethod|null
     */
    protected function getConstructor(ReflectionClass $reflectionClass): ?ReflectionMethod
    {
        $constructor = Reflect::method($reflectionClass, '__construct');

        if ($constructor
            && $constructor->isPublic()
            && ! $constructor->isAbstract()
            && ! $constructor->isStatic()
        ) {
            return $constructor;
        }

        return null;
    }
}
