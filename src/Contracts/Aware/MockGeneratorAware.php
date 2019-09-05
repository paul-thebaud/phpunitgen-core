<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Aware;

use PhpUnitGen\Core\Contracts\Generators\MockGenerator;

/**
 * Interface MockGeneratorAware.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface MockGeneratorAware
{
    /**
     * @return MockGenerator
     */
    public function getMockGenerator(): MockGenerator;

    /**
     * @param MockGenerator $config
     */
    public function setMockGenerator(MockGenerator $config): void;
}
