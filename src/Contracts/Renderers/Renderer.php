<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Renderers;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestParameter;
use PhpUnitGen\Core\Models\TestProperty;
use PhpUnitGen\Core\Models\TestProvider;
use PhpUnitGen\Core\Models\TestStatement;
use PhpUnitGen\Core\Models\TestTrait;

/**
 * Interface Renderer.
 *
 * An object which can visit and render all Renderable implementations.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface Renderer
{
    /**
     * Visit and render a test import.
     *
     * @param TestImport $import
     */
    public function visitTestImport(TestImport $import): void;

    /**
     * Visit and render a test class.
     *
     * @param TestClass $class
     */
    public function visitTestClass(TestClass $class): void;

    /**
     * Visit and render a test trait.
     *
     * @param TestTrait $trait
     */
    public function visitTestTrait(TestTrait $trait): void;

    /**
     * Visit and render a test property.
     *
     * @param TestProperty $property
     */
    public function visitTestProperty(TestProperty $property): void;

    /**
     * Visit and render a test method.
     *
     * @param TestMethod $method
     */
    public function visitTestMethod(TestMethod $method): void;

    /**
     * Visit and render a test parameter.
     *
     * @param TestParameter $parameter
     */
    public function visitTestParameter(TestParameter $parameter): void;

    /**
     * Visit and render a test provider.
     *
     * @param TestProvider $provider
     */
    public function visitTestProvider(TestProvider $provider): void;

    /**
     * Visit and render a test statement.
     *
     * @param TestStatement $statement
     */
    public function visitTestStatement(TestStatement $statement): void;
}
