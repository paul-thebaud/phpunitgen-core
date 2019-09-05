<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Aware;

use PhpUnitGen\Core\Contracts\Generators\Factories\ClassFactory;

/**
 * Interface ClassFactoryAware.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface ClassFactoryAware
{
    /**
     * @return ClassFactory
     */
    public function getClassFactory(): ClassFactory;

    /**
     * @param ClassFactory $config
     */
    public function setClassFactory(ClassFactory $config): void;
}
