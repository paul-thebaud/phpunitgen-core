<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Aware;

use PhpUnitGen\Core\Contracts\Generators\Factories\TypeFactory;

/**
 * Interface TypeFactoryAware.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface TypeFactoryAware
{
    /**
     * @return TypeFactory
     */
    public function getTypeFactory(): TypeFactory;

    /**
     * @param TypeFactory $config
     */
    public function setTypeFactory(TypeFactory $config): void;
}
