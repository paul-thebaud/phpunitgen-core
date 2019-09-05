<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel;

use PhpUnitGen\Core\Generators\Factories\ClassFactory;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Class UnitClassFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class UnitClassFactory extends ClassFactory
{
    /**
     * {@inheritdoc}
     */
    protected function makeNamespace(ReflectionClass $reflectionClass): string
    {
        return parent::makeNamespace($reflectionClass).'\\Unit';
    }
}
