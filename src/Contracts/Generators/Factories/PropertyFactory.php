<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators\Factories;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestProperty;
use Roave\BetterReflection\Reflection\ReflectionParameter;

/**
 * Interface PropertyFactory.
 *
 * A factory for class/properties/methods documentation.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface PropertyFactory
{
    /**
     * Create the property for the class to test instantiation.
     *
     * @param TestClass $class
     *
     * @return TestProperty
     */
    public function makeForClass(TestClass $class): TestProperty;

    /**
     * Create the property for a class parameter.
     *
     * @param TestClass           $class
     * @param ReflectionParameter $reflectionParameter
     *
     * @return TestProperty
     */
    public function makeForParameter(TestClass $class, ReflectionParameter $reflectionParameter): TestProperty;

    /**
     * Create the property for a class with custom specs.
     *
     * @param TestClass $class
     * @param string    $name
     * @param string    $type
     * @param bool      $isBuiltIn
     * @param bool      $isMock
     *
     * @return TestProperty
     */
    public function makeCustom(
        TestClass $class,
        string $name,
        string $type,
        bool $isBuiltIn = false,
        bool $isMock = true
    ): TestProperty;
}
