<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Aware;

use PhpUnitGen\Core\Contracts\Aware\ImportFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory;

/**
 * Trait ImportFactoryAwareTrait.
 *
 * @see     ImportFactoryAware
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait ImportFactoryAwareTrait
{
    /**
     * @var ImportFactory
     */
    protected $importFactory;

    /**
     * {@inheritdoc}
     */
    public function setImportFactory(ImportFactory $importFactory): void
    {
        $this->importFactory = $importFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getImportFactory(): ImportFactory
    {
        return $this->importFactory;
    }
}
