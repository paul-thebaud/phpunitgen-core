<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Aware;

use PhpUnitGen\Core\Contracts\Generators\TestGenerator;

/**
 * Interface TestGeneratorAware.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface TestGeneratorAware
{
    /**
     * @return TestGenerator
     */
    public function getTestGenerator(): TestGenerator;

    /**
     * @param TestGenerator $config
     */
    public function setTestGenerator(TestGenerator $config): void;
}
