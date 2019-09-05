<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators\Factories;

use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Interface MethodFactory.
 *
 * A factory for various methods creation.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface MethodFactory
{
    /**
     * Create the "setUp" method for the class.
     *
     * @param TestClass $class
     *
     * @return TestMethod
     */
    public function makeSetUp(TestClass $class): TestMethod;

    /**
     * Create the "tearDown" method for the class.
     *
     * @param TestClass $class
     *
     * @return TestMethod
     */
    public function makeTearDown(TestClass $class): TestMethod;

    /**
     * Create an empty method and append suffix to its name.
     *
     * @param ReflectionMethod $reflectionMethod
     * @param string           $suffix
     *
     * @return TestMethod
     */
    public function makeEmpty(ReflectionMethod $reflectionMethod, string $suffix = ''): TestMethod;

    /**
     * Create an incomplete method (when it cannot have automatic generation or it is disabled).
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return TestMethod
     */
    public function makeIncomplete(ReflectionMethod $reflectionMethod): TestMethod;

    /**
     * Create method(s) for testable reflection method and it (or them) to the test class.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     *
     * @throws InvalidArgumentException
     */
    public function makeTestable(TestClass $class, ReflectionMethod $reflectionMethod): void;
}
