<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Aware;

use PhpUnitGen\Core\Contracts\Aware\ClassFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\ClassFactory;

/**
 * Trait ClassFactoryAwareTrait.
 *
 * @see     ClassFactoryAware
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait ClassFactoryAwareTrait
{
    /**
     * @var ClassFactory
     */
    protected $classFactory;

    /**
     * {@inheritdoc}
     */
    public function setClassFactory(ClassFactory $classFactory): void
    {
        $this->classFactory = $classFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassFactory(): ClassFactory
    {
        return $this->classFactory;
    }
}
