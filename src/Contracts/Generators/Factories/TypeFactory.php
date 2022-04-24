<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators\Factories;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use Tightenco\Collect\Support\Collection;

/**
 * Interface TypeFactory.
 *
 * A factory for documentation/properties/parameters types.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface TypeFactory
{
    /**
     * Get the type to use from the string version of type.
     *
     * @param TestClass $class
     * @param string    $type
     * @param bool      $isBuiltIn
     *
     * @return TestImport|string
     */
    public function makeFromString(TestClass $class, string $type, bool $isBuiltIn): TestImport|string;

    /**
     * Format a string or TestImport type.
     *
     * @param TestImport|string $type
     *
     * @return string
     */
    public function formatType(TestImport|string $type): string;

    /**
     * Join the given types list into a PHP style string type.
     *
     * @param Collection $types
     * @param string     $separator
     *
     * @return string
     */
    public function formatTypes(Collection $types, string $separator = '|'): string;
}
