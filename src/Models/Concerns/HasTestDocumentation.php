<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models\Concerns;

use PhpUnitGen\Core\Models\TestDocumentation;

/**
 * Trait HasTestClassParent.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait HasTestDocumentation
{
    /**
     * @var TestDocumentation The test documentation.
     */
    protected $documentation;

    /**
     * @return TestDocumentation
     */
    public function getDocumentation(): TestDocumentation
    {
        return $this->documentation;
    }

    /**
     * @param TestDocumentation $documentation
     *
     * @return static
     */
    public function setDocumentation(TestDocumentation $documentation): self
    {
        $this->documentation = $documentation;

        return $this;
    }
}
