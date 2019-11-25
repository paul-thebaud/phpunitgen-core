<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Concerns;

use PhpUnitGen\Core\Aware\StatementFactoryAwareTrait;
use PhpUnitGen\Core\Aware\ValueFactoryAwareTrait;
use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Tightenco\Collect\Support\Collection;

/**
 * Trait MocksParameters.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait MocksParameters
{
    use StatementFactoryAwareTrait;
    use ValueFactoryAwareTrait;

    protected function mockParametersAndAddStatements(TestMethod $method, Collection $parameters): void
    {
        $parametersNotEmpty = $parameters
            ->each(function (ReflectionParameter $reflectionParameter) use ($method) {
                $name = $reflectionParameter->getName();
                $value = $this->valueFactory->make(
                    $method->getTestClass(),
                    Reflect::parameterType($reflectionParameter)
                );

                $method->addStatement(
                    $this->statementFactory->makeAffect($name, $value, false)
                );
            })
            ->isNotEmpty();

        if ($parametersNotEmpty) {
            $method->addStatement(new TestStatement(''));
        }
    }
}
