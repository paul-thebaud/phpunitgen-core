<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators;

use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Interface TestGenerator.
 *
 * A strategy to generate test classes.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface TestGenerator
{
    /**
     * Generate the tests models for the given reflection class.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return TestClass
     *
     * @throws InvalidArgumentException
     */
    public function generate(ReflectionClass $reflectionClass): TestClass;

    /**
     * Check if the test generator can generate models for the given reflection class.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return bool
     */
    public function canGenerateFor(ReflectionClass $reflectionClass): bool;
}
