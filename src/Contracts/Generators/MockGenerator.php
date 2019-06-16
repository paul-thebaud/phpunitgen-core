<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionParameter;

/**
 * Interface MockGenerator.
 *
 * A strategy to generate mock properties and statements.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface MockGenerator
{
    /**
     * Generate the mock property and instantiation statement for the given constructor parameter and setUp method.
     *
     * @param TestMethod          $method
     * @param ReflectionParameter $parameter
     */
    public function generateForParameter(TestMethod $method, ReflectionParameter $parameter): void;

    /**
     * Generate the mock instantiation statement without affectation to a property. Returns null if it cannot be mocked.
     *
     * @param TestClass           $class
     * @param ReflectionParameter $parameter
     *
     * @return TestStatement|null
     */
    public function generateStatement(TestClass $class, ReflectionParameter $parameter): ?TestStatement;
}
