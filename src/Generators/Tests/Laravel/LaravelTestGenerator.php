<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel;

use PhpUnitGen\Core\Contracts\Generators\Factories\ClassFactory as ClassFactoryContract;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicTestGenerator;

/**
 * Class LaravelTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class LaravelTestGenerator extends BasicTestGenerator
{
    /**
     * {@inheritdoc}
     */
    public static function implementations(): array
    {
        return array_merge(parent::implementations(), [
            ClassFactoryContract::class => UnitClassFactory::class,
        ]);
    }
}
