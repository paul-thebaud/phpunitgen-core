<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators\Factories;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestStatement;
use Tightenco\Collect\Support\Collection;

/**
 * Interface StatementFactory.
 *
 * A factory for class/properties/methods documentation.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface StatementFactory
{
    /**
     * Create a statement with a "@todo" annotation.
     *
     * @param string $todo
     *
     * @return TestStatement
     */
    public function makeTodo(string $todo): TestStatement;

    /**
     * Create an affect statement for the given name and value. Use "$this" if it is a property.
     *
     * @param string $name
     * @param string $value
     * @param bool   $isProperty
     *
     * @return TestStatement
     */
    public function makeAffect(string $name, string $value, bool $isProperty = true): TestStatement;

    /**
     * Create a PHPUnit assert statement for the given assert type (same, true...) and parameters.
     *
     * @param string   $assert
     * @param string[] $parameters
     *
     * @return TestStatement
     */
    public function makeAssert(string $assert, string ...$parameters): TestStatement;

    /**
     * Create the class instantiation statement.
     *
     * @param TestClass  $class
     * @param Collection $parameters
     *
     * @return TestStatement
     */
    public function makeInstantiation(TestClass $class, Collection $parameters): TestStatement;
}
