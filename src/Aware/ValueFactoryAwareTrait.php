<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Aware;

use PhpUnitGen\Core\Contracts\Aware\ValueFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory;

/**
 * Trait ValueFactoryAwareTrait.
 *
 * @see     ValueFactoryAware
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait ValueFactoryAwareTrait
{
    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * {@inheritdoc}
     */
    public function setValueFactory(ValueFactory $valueFactory): void
    {
        $this->valueFactory = $valueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getValueFactory(): ValueFactory
    {
        return $this->valueFactory;
    }
}
