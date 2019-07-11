<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators\Factories;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;

/**
 * Interface ImportFactory.
 *
 * A factory for import of test class.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface ImportFactory
{
    /**
     * Create an import for the given type and add it to the given class if not already added.
     *
     * @param TestClass $class
     * @param string    $type
     *
     * @return TestImport
     */
    public function make(TestClass $class, string $type): TestImport;
}
