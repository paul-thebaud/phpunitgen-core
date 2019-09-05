<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Aware;

use PhpUnitGen\Core\Contracts\Aware\MethodFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory;

/**
 * Trait MethodFactoryAwareTrait.
 *
 * @see     MethodFactoryAware
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait MethodFactoryAwareTrait
{
    /**
     * @var MethodFactory
     */
    protected $methodFactory;

    /**
     * {@inheritdoc}
     */
    public function setMethodFactory(MethodFactory $importFactory): void
    {
        $this->methodFactory = $importFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getMethodFactory(): MethodFactory
    {
        return $this->methodFactory;
    }
}
