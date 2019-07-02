<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Factories;

use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionType;

/**
 * Class ValueFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class ValueFactory
{
    /**
     * @var MockGenerator
     */
    protected $mockGenerator;

    /**
     * ValueFactory constructor.
     *
     * @param MockGenerator $mockGenerator
     */
    public function __construct(MockGenerator $mockGenerator)
    {
        $this->mockGenerator = $mockGenerator;
    }

    /**
     * Generate a PHP value for the given type.
     *
     * @param TestClass           $class
     * @param ReflectionType|null $reflectionType
     *
     * @return string
     */
    public function create(TestClass $class, ?ReflectionType $reflectionType): string
    {
        if (! $reflectionType) {
            return 'null';
        }

        $type = (string) $reflectionType;

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
        switch ($type) {
            case 'int':
                return '42';
            case 'float':
                return '42.42';
            case 'string':
                return '\'42\'';
            case 'bool':
                return 'true';
            case 'callable':
                return 'function () {}';
            case 'array':
            case 'iterable':
                return '[]';
            case 'object':
                return 'new \\stdClass()';
            case 'self':
            case 'parent':
                return $this->mockGenerator->generateMock($class, $class->getReflectionClass()->getShortName());
            case 'void':
            default:
                return 'null';
        }
    }
}
