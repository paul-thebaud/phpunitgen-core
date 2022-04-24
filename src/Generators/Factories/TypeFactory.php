<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Factories;

use PhpUnitGen\Core\Aware\ImportFactoryAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\ImportFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\TypeFactory as TypeFactoryContract;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use Tightenco\Collect\Support\Collection;

/**
 * Class TypeFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TypeFactory implements ImportFactoryAware, TypeFactoryContract
{
    use ImportFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function makeFromString(TestClass $class, string $type, bool $isBuiltIn): TestImport|string
    {
        if (in_array($type, ['parent', 'self', 'static'])) {
            return $this->importFactory->make($class, $class->getReflectionClass()->getName());
        }

        if ($isBuiltIn) {
            return $type;
        }

        return $this->importFactory->make($class, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function formatType(string|TestImport $type): string
    {
        return is_string($type) ? $type : $type->getFinalName();
    }

    /**
     * {@inheritdoc}
     */
    public function formatTypes(Collection $types, string $separator = '|'): string
    {
        return $types->map(fn (TestImport|string $t) => $this->formatType($t))->implode($separator);
    }
}
