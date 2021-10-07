<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Generators;

use Roave\BetterReflection\Reflection\ReflectionClass;

interface DelegateTestGenerator extends TestGenerator
{
    /**
     * Retrieve the delegate for the given reflection class.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return TestGenerator
     */
    public function getDelegate(ReflectionClass $reflectionClass): TestGenerator;
}
