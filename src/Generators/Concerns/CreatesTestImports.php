<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Concerns;

use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;

/**
 * Trait CreatesTestImports.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait CreatesTestImports
{
    /**
     * Create the import for the given class if not already created. Returns the short name to use.
     *
     * @param TestClass $testClass
     * @param string    $class
     *
     * @return string
     */
    protected function createTestImport(TestClass $testClass, string $class): string
    {
        $import = $testClass->getImports()
            ->first(function (TestImport $import) use ($class) {
                return $import->getName() === $class;
            });
        if ($import) {
            return $import->getFinalName();
        }

        $shortName = Str::afterLast('\\', $class);

        do {
            $aliased = $testClass->getImports()
                ->contains(function (TestImport $import) use (&$shortName) {
                    if ($import->getFinalName() === $shortName) {
                        $shortName .= 'Alias';

                        return true;
                    }

                    return false;
                });
        } while ($aliased);

        $alias = Str::afterLast('\\', $class) === $shortName ? null : $shortName;

        return (new TestImport($testClass, $class, $alias))->getFinalName();
    }
}
