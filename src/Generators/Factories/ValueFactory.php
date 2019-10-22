<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Factories;

use PhpUnitGen\Core\Aware\MockGeneratorAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\MockGeneratorAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory as ValueFactoryContract;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Reflection\ReflectionType;

/**
 * Class ValueFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class ValueFactory implements ValueFactoryContract, MockGeneratorAware
{
    use MockGeneratorAwareTrait;

    /**
     * Mapping between built in types and values.
     */
    protected const BUILT_IN_VALUES = [
        'int'      => '42',
        'float'    => '42.42',
        'string'   => '\'42\'',
        'bool'     => 'true',
        'callable' => 'function () {}',
        'array'    => '[]',
        'iterable' => '[]',
        'object'   => 'new \\stdClass()',
    ];

    /**
     * {@inheritdoc}
     */
    public function make(TestClass $class, ?ReflectionType $reflectionType): string
    {
        if (! $reflectionType) {
            return 'null';
        }

        $type = $reflectionType->getType();

        if ($reflectionType->isBuiltin()) {
            return $this->createForBuiltIn($class, $type);
        }

        return $this->mockGenerator->generateMock($class, $type);
    }

    /**
     * Create a value for a built in type.
     *
     * @param TestClass $class
     * @param string    $type
     *
     * @return string
     */
    protected function createForBuiltIn(TestClass $class, string $type): string
    {
        // The built in type reference a class, so mock it.
        if ($type === 'self' || $type === 'parent') {
            return $this->mockGenerator->generateMock($class, $class->getReflectionClass()->getShortName());
        }

        return static::BUILT_IN_VALUES[$type] ?? 'null';
    }
}
