<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Job;

use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Concerns\ChecksMethods;
use PhpUnitGen\Core\Generators\Tests\Concerns\MocksParameters;
use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;

/**
 * Class JobMethodFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class JobMethodFactory extends BasicMethodFactory
{
    use ChecksMethods;
    use MocksParameters;

    /**
     * {@inheritdoc}
     */
    public function makeTestable(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        if ($this->isGetterOrSetter($reflectionMethod)) {
            parent::makeTestable($class, $reflectionMethod);

            return;
        }

        if (! $this->isMethod($reflectionMethod, 'handle')) {
            throw new InvalidArgumentException(
                "cannot generate tests for method {$reflectionMethod->getShortName()}, not a \"handle\" method"
            );
        }

        $method = $this->makeEmpty($reflectionMethod);
        $class->addMethod($method);

        $instanceName = $this->getPropertyName($class->getReflectionClass());
        $parameters = Reflect::parameters($reflectionMethod);

        $this->mockParametersAndAddStatements($method, $parameters);

        $method->addStatement($this->statementFactory->makeTodo('This test is incomplete.'));
        $method->addStatement(
            (new TestStatement('$this->'))
                ->append($instanceName)
                ->append('->handle(')
                ->append($parameters->map(function (ReflectionParameter $reflectionParameter) {
                    return '$'.$reflectionParameter->getName();
                })->join(', '))
                ->append(')')
        );
    }
}
