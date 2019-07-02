<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Mocks;

use PhpUnitGen\Core\Contracts\Generators\ImportFactory;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator;

/**
 * Class AbstractMockGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
abstract class AbstractMockGenerator implements MockGenerator
{
    /**
     * @var ImportFactory
     */
    protected $importFactory;

    /**
     * AbstractMockGenerator constructor.
     *
     * @param ImportFactory $importFactory
     */
    public function __construct(ImportFactory $importFactory)
    {
        $this->importFactory = $importFactory;
    }
}
