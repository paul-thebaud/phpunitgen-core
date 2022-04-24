<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators\Factories;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use Tightenco\Collect\Support\Collection;

/**
 * Interface DocumentationFactory.
 *
 * A factory for class/properties/methods documentation.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface DocumentationFactory
{
    /**
     * Create the documentation for a test class.
     *
     * @param TestClass $class
     *
     * @return TestDocumentation
     */
    public function makeForClass(TestClass $class): TestDocumentation;

    /**
     * Create the documentation for a test class property with the given type(s).
     *
     * @param TestProperty $property
     * @param Collection   $types
     *
     * @return TestDocumentation
     */
    public function makeForProperty(TestProperty $property, Collection $types): TestDocumentation;

    /**
     * Create the documentation for an inherited method.
     *
     * @param TestMethod $method
     *
     * @return TestDocumentation
     */
    public function makeForInheritedMethod(TestMethod $method): TestDocumentation;
}
