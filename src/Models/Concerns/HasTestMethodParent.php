<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models\Concerns;

use PhpUnitGen\Core\Models\TestMethod;

/**
 * Trait HasTestMethodParent.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait HasTestMethodParent
{
    /**
     * @var TestMethod $testMethod The parent test method.
     */
    protected $testMethod;

    /**
     * @return TestMethod
     */
    public function getTestMethod(): TestMethod
    {
        return $this->testMethod;
    }
}
