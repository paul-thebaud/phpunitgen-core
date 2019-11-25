<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Policy;

use PhpUnitGen\Core\Contracts\Aware\ConfigAware;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Concerns\MocksParameters;
use PhpUnitGen\Core\Generators\Tests\Laravel\Concerns\HasInstanceBinding;
use PhpUnitGen\Core\Generators\Tests\Laravel\Concerns\UsesUserModel;
use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Tightenco\Collect\Support\Collection;

/**
 * Class PolicyMethodFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class PolicyMethodFactory extends BasicMethodFactory implements ConfigAware
{
    use HasInstanceBinding;
    use MocksParameters;
    use UsesUserModel {
        UsesUserModel::setStatementFactory insteadof MocksParameters;
        UsesUserModel::getStatementFactory insteadof MocksParameters;
    }

    /**
     * {@inheritdoc}
     */
    public function makeSetUp(TestClass $class): TestMethod
    {
        $method = parent::makeSetUp($class);

        $this->makeUserAffectStatement($class, $method);
        $method->addStatement(
            $this->makeInstanceBindingStatement($class->getReflectionClass())
        );

        return $method;
    }

    /**
     * {@inheritdoc}
     */
    public function makeTestable(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        if ($this->isGetterOrSetter($reflectionMethod)) {
            parent::makeTestable($class, $reflectionMethod);

            return;
        }

        if ($reflectionMethod->isStatic()) {
            throw new InvalidArgumentException(
                "cannot generate tests for method {$reflectionMethod->getShortName()}, policy method cannot be static"
            );
        }

        $this->addPolicyMethod($class, $reflectionMethod, 'false', 'Unauthorized');
        $this->addPolicyMethod($class, $reflectionMethod, 'true', 'Authorized');
    }

    /**
     * Add a method to test a policy method for authorized or unauthorized user.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     * @param string           $expected
     * @param string           $nameSuffix
     */
    protected function addPolicyMethod(
        TestClass $class,
        ReflectionMethod $reflectionMethod,
        string $expected,
        string $nameSuffix
    ): void {
        $method = $this->makeEmpty($reflectionMethod, 'When'.$nameSuffix);
        $class->addMethod($method);
        $method->addStatement($this->statementFactory->makeTodo('This test is incomplete.'));

        $parameters = Reflect::parameters($reflectionMethod)->forget(0);

        $this->mockParametersAndAddStatements($method, $parameters);
        $parametersString = $this->getParametersString($class->getReflectionClass(), $parameters);

        $methodCall = '$this->user->can(\''.$reflectionMethod->getShortName().'\', '.$parametersString.')';

        $method->addStatement(
            $this->statementFactory->makeAssert($expected, $methodCall)
        );
    }

    /**
     * Retrieve the parameters as they will be added in method argument.
     *
     * @param ReflectionClass $reflectionClass
     * @param Collection      $parameters
     *
     * @return string
     */
    protected function getParametersString(ReflectionClass $reflectionClass, Collection $parameters): string
    {
        $parameters = $parameters
            ->map(function (ReflectionParameter $reflectionParameter) {
                return '$'.$reflectionParameter->getName();
            });

        if ($parameters->count() < 1) {
            return '['.$reflectionClass->getShortName().'::class]';
        }

        $parametersString = $parameters->implode(', ');

        if ($parameters->count() > 1) {
            return '['.$parametersString.']';
        }

        return $parametersString;
    }
}
