<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators;

use PhpUnitGen\Core\Contracts\Generators\TestGenerator;
use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Class BasicTestGenerator.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class BasicTestGenerator implements TestGenerator
{
    /**
     * {@inheritDoc}
     */
    public function generate(ReflectionClass $class): TestClass
    {
        // TODO
        return new TestClass($class, 'TODO');
    }
}
