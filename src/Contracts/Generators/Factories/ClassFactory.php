<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators\Factories;

use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Interface ClassFactory.
 *
 * A factory for test class.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface ClassFactory
{
    /**
     * Create an empty test class from the given reflection class.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return TestClass
     */
    public function make(ReflectionClass $reflectionClass): TestClass;

    /**
     * Get the base namespace of a test case.
     *
     * @return string
     */
    public function getTestBaseNamespace(): string;

    /**
     * Get the sub namespace of a test case (for example "Unit" or "Feature" on Laravel).
     *
     * @return string
     */
    public function getTestSubNamespace(): string;
}
