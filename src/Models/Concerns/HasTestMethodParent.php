<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models\Concerns;

use PhpUnitGen\Core\Models\TestMethod;

/**
 * Trait HasTestMethodParent.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait HasTestMethodParent
{
    /**
     * @var TestMethod The parent test method.
     */
    protected $testMethod;

    /**
     * @return TestMethod
     */
    public function getTestMethod(): TestMethod
    {
        return $this->testMethod;
    }

    /**
     * @param TestMethod $testMethod
     *
     * @return static
     */
    public function setTestMethod(TestMethod $testMethod): self
    {
        $this->testMethod = $testMethod;

        return $this;
    }
}
