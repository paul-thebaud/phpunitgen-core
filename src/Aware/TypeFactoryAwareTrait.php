<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Aware;

use PhpUnitGen\Core\Contracts\Aware\TypeFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\TypeFactory;

/**
 * Trait TypeFactoryAwareTrait.
 *
 * @see     TypeFactoryAware
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait TypeFactoryAwareTrait
{
    /**
     * @var TypeFactory
     */
    protected $typeFactory;

    /**
     * {@inheritdoc}
     */
    public function setTypeFactory(TypeFactory $typeFactory): void
    {
        $this->typeFactory = $typeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getTypeFactory(): TypeFactory
    {
        return $this->typeFactory;
    }
}
