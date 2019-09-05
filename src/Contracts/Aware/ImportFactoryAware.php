<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Aware;

use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory;

/**
 * Interface ImportFactoryAware.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface ImportFactoryAware
{
    /**
     * @return ImportFactory
     */
    public function getImportFactory(): ImportFactory;

    /**
     * @param ImportFactory $config
     */
    public function setImportFactory(ImportFactory $config): void;
}
