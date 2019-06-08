<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;

/**
 * Interface MockGenerator.
 *
 * A strategy to generate mock properties and statements.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface MockGenerator
{
    /**
     * Generate the mock property for the given parameter.
     *
     * @param TestClass           $class
     * @param ReflectionParameter $parameter
     */
    public function generateProperty(TestClass $class, ReflectionParameter $parameter): void;

    /**
     * Generate the mock creation statement for the given parameter.
     *
     * @param TestMethod          $method
     * @param ReflectionParameter $parameter
     */
    public function generateStatement(TestMethod $method, ReflectionParameter $parameter): void;
}
