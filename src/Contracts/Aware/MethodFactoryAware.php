<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Aware;

use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory;

/**
 * Interface MethodFactoryAware.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface MethodFactoryAware
{
    /**
     * @return MethodFactory
     */
    public function getMethodFactory(): MethodFactory;

    /**
     * @param MethodFactory $config
     */
    public function setMethodFactory(MethodFactory $config): void;
}
