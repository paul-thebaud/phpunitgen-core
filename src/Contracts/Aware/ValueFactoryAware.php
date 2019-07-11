<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Aware;

use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory;

/**
 * Interface ValueFactoryAware.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface ValueFactoryAware
{
    /**
     * @return ValueFactory
     */
    public function getValueFactory(): ValueFactory;

    /**
     * @param ValueFactory $config
     */
    public function setValueFactory(ValueFactory $config): void;
}
