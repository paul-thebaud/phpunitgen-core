<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators;

use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Interface TestGenerator.
 *
 * A strategy to generate test classes.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface TestGenerator
{
    /**
     * Generate the tests models for the given class.
     *
     * @param ReflectionClass $class
     *
     * @return TestClass
     */
    public function generate(ReflectionClass $class): TestClass;
}
