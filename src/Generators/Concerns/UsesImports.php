<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Concerns;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;

/**
 * Trait UsesImports.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait UsesImports
{
    /**
     * Create the import for the given class if not already created. Returns the short name to use.
     *
     * @param TestClass $testClass
     * @param string    $class
     *
     * @return string
     */
    protected function importClass(TestClass $testClass, string $class): string
    {
        $import = $testClass->getImports()
            ->first(function (TestImport $import) use ($class) {
                return $import->getName() === $class;
            });
        if ($import) {
            return $this->getNameFor($import);
        }

        $shortName = $this->getShortName($class);

        do {
            $aliased = $testClass->getImports()
                ->contains(function (TestImport $import) use (&$shortName) {
                    if ($this->getNameFor($import) === $shortName) {
                        $shortName .= 'Alias';

                        return true;
                    }

                    return false;
                });
        } while ($aliased);

        $alias = $this->getShortName($class) === $shortName ? null : $shortName;

        return $this->getNameFor(new TestImport($testClass, $class, $alias));
    }

    /**
     * Get the short name for the given import.
     *
     * @param TestImport $import
     *
     * @return string
     */
    protected function getNameFor(TestImport $import): string
    {
        return $import->getAlias() ?? $this->getShortName($import->getName());
    }

    /**
     * Get the short name for the given class.
     *
     * @param string $class
     *
     * @return string
     */
    protected function getShortName(string $class): string
    {
        $lastPosition = strrpos($class, '\\');
        if ($lastPosition === false) {
            return $class;
        }

        return substr($class, $lastPosition + 1);
    }
}
