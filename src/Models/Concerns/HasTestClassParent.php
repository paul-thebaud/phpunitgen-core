<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models\Concerns;

use PhpUnitGen\Core\Models\TestClass;

/**
 * Trait HasTestClassParent.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait HasTestClassParent
{
    /**
     * @var TestClass $testClass The parent test class.
     */
    protected $testClass;

    /**
     * @return TestClass
     */
    public function getTestClass(): TestClass
    {
        return $this->testClass;
    }
}
