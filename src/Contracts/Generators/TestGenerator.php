<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators;

use PhpUnitGen\Core\Contracts\Generators\Factories\ClassFactory;
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
     * Get the implementations that this generator will use (for factories).
     *
     * @return array
     */
    public static function implementations(): array;

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

    /**
     * Retrieve the class factory (used when generating path of test case on command line).
     *
     * @return ClassFactory
     */
    public function getClassFactory(): ClassFactory;
}
