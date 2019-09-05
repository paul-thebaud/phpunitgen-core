<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;

/**
 * Interface MockGenerator.
 *
 * A strategy to generate mock.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface MockGenerator
{
    /**
     * Get the mock to document a mocked property.
     *
     * @param TestClass $class
     *
     * @return TestImport
     */
    public function getMockType(TestClass $class): TestImport;

    /**
     * Returns the mock creation for the given reflection type as a string.
     *
     * @param TestClass   $class
     * @param string|null $type
     *
     * @return string
     */
    public function generateMock(TestClass $class, string $type): string;
}
