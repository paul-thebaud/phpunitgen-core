<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel;

use PhpUnitGen\Core\Generators\Factories\ClassFactory;

/**
 * Class FeatureClassFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class FeatureClassFactory extends ClassFactory
{
    /**
     * {@inheritdoc}
     */
    public function getTestSubNamespace(): string
    {
        return '\\Feature';
    }
}
