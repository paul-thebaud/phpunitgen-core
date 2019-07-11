<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Aware;

use PhpUnitGen\Core\Contracts\Aware\MockGeneratorAware;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator;

/**
 * Trait MockGeneratorAwareTrait.
 *
 * @see     MockGeneratorAware
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait MockGeneratorAwareTrait
{
    /**
     * @var MockGenerator
     */
    protected $mockGenerator;

    /**
     * {@inheritdoc}
     */
    public function setMockGenerator(MockGenerator $mockGenerator): void
    {
        $this->mockGenerator = $mockGenerator;
    }

    /**
     * {@inheritdoc}
     */
    public function getMockGenerator(): MockGenerator
    {
        return $this->mockGenerator;
    }
}
