<?php

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Concerns;

use PhpUnitGen\Core\Generators\Concerns\InstantiatesClass;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Trait HasInstanceBinding.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait HasInstanceBinding
{
    use InstantiatesClass;

    /**
     * Make a statement which bind the tested instance on Laravel app.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return TestStatement
     */
    protected function makeInstanceBindingStatement(ReflectionClass $reflectionClass): TestStatement
    {
        return (new TestStatement('$this->app->instance('))
            ->append($reflectionClass->getShortName())
            ->append('::class, $this->')
            ->append($this->getPropertyName($reflectionClass))
            ->append(')');
    }
}
