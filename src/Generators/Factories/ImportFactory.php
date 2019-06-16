<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Factories;

use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;

/**
 * Class ImportFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class ImportFactory
{
    /**
     * Create an import for the given type and add it to the given class if not already added.
     *
     * @param TestClass $class
     * @param string    $type
     *
     * @return TestImport
     */
    public function create(TestClass $class, string $type): TestImport
    {
        $import = $class->getImports()
            ->first(function (TestImport $import) use ($type) {
                return $import->getName() === $type;
            });

        if ($import) {
            return $import;
        }

        $shortName = Str::afterLast('\\', $type);

        do {
            $aliased = $class->getImports()
                ->contains(function (TestImport $import) use (&$shortName) {
                    if ($import->getFinalName() === $shortName) {
                        $shortName .= 'Alias';

                        return true;
                    }

                    return false;
                });
        } while ($aliased);

        $alias = Str::afterLast('\\', $type) === $shortName ? null : $shortName;

        $import = new TestImport($type, $alias);
        $class->addImport($import);

        return $import;
    }
}
