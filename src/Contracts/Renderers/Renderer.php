<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Renderers;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
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
     * Get the rendered content after visiting objects.
     *
     * @return Rendered
     */
    public function getRendered(): Rendered;

    /**
     * Visit and render a test import.
     *
     * @param TestImport $import
     *
     * @return static
     */
    public function visitTestImport(TestImport $import): self;

    /**
     * Visit and render a test class.
     *
     * @param TestClass $class
     *
     * @return static
     */
    public function visitTestClass(TestClass $class): self;

    /**
     * Visit and render a test trait.
     *
     * @param TestTrait $trait
     *
     * @return static
     */
    public function visitTestTrait(TestTrait $trait): self;

    /**
     * Visit and render a test property.
     *
     * @param TestProperty $property
     *
     * @return static
     */
    public function visitTestProperty(TestProperty $property): self;

    /**
     * Visit and render a test method.
     *
     * @param TestMethod $method
     *
     * @return static
     */
    public function visitTestMethod(TestMethod $method): self;

    /**
     * Visit and render a test parameter.
     *
     * @param TestParameter $parameter
     *
     * @return static
     */
    public function visitTestParameter(TestParameter $parameter): self;

    /**
     * Visit and render a test provider.
     *
     * @param TestProvider $provider
     *
     * @return static
     */
    public function visitTestProvider(TestProvider $provider): self;

    /**
     * Visit and render a test statement.
     *
     * @param TestStatement $statement
     *
     * @return static
     */
    public function visitTestStatement(TestStatement $statement): self;

    /**
     * Visit and render a test documentation.
     *
     * @param TestDocumentation $documentation
     *
     * @return static
     */
    public function visitTestDocumentation(TestDocumentation $documentation): self;
}
