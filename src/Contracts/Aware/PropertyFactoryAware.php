<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Aware;

use PhpUnitGen\Core\Contracts\Generators\Factories\PropertyFactory;

/**
 * Interface DocumentationFactoryAware.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface PropertyFactoryAware
{
    /**
     * @return PropertyFactory
     */
    public function getPropertyFactory(): PropertyFactory;

    /**
     * @param PropertyFactory $config
     */
    public function setPropertyFactory(PropertyFactory $config): void;
}
