<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Aware;

use PhpUnitGen\Core\Contracts\Aware\TestGeneratorAware;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator;

/**
 * Trait TestGeneratorAwareTrait.
 *
 * @see     TestGeneratorAware
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait TestGeneratorAwareTrait
{
    /**
     * @var TestGenerator
     */
    protected $testGenerator;

    /**
     * {@inheritdoc}
     */
    public function setTestGenerator(TestGenerator $testGenerator): void
    {
        $this->testGenerator = $testGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getTestGenerator(): TestGenerator
    {
        return $this->testGenerator;
    }
}
