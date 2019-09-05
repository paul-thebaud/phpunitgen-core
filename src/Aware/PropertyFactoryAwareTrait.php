<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Aware;

use PhpUnitGen\Core\Contracts\Aware\PropertyFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\PropertyFactory;

/**
 * Trait PropertyFactoryAwareTrait.
 *
 * @see     PropertyFactoryAware
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait PropertyFactoryAwareTrait
{
    /**
     * @var PropertyFactory
     */
    protected $propertyFactory;

    /**
     * {@inheritdoc}
     */
    public function setPropertyFactory(PropertyFactory $propertyFactory): void
    {
        $this->propertyFactory = $propertyFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyFactory(): PropertyFactory
    {
        return $this->propertyFactory;
    }
}
