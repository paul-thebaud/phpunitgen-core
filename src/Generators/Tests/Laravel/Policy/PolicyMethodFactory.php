<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Policy;

use PhpUnitGen\Core\Aware\ConfigAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\ConfigAware;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Laravel\UsesUserModel;
use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestStatement;
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
    use ConfigAwareTrait;
    use UsesUserModel;

    /**
     * {@inheritdoc}
     */
    public function makeSetUp(TestClass $class): TestMethod
    {
        $method = new TestMethod('setUp', TestMethod::VISIBILITY_PROTECTED);

        $this->makeMethodInherited($method);

        $userImport = $this->getUserClass($class)->getFinalName();

        $method->addStatement(
            $this->statementFactory->makeAffect('user', "new {$userImport}()")
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
        } else {
            $this->addPolicyMethod($class, $reflectionMethod, 'false', 'Unauthorized');
            $this->addPolicyMethod($class, $reflectionMethod, 'true', 'Authorized');
        }
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

        $parameters = Reflect::parameters($reflectionMethod)->forget(0);

        $this->addParametersStatements($method, $parameters);
        $parametersString = $this->getParametersString($parameters);

        $methodCall = '$this->user->can(\''.$reflectionMethod->getShortName().'\', '.$parametersString.')';

        $method->addStatement(
            $this->statementFactory->makeAssert($expected, $methodCall)
        );
    }

    /**
     * Add the policy method parameters affectation statements.
     *
     * @param TestMethod $method
     * @param Collection $parameters
     */
    protected function addParametersStatements(TestMethod $method, Collection $parameters): void
    {
        $parametersNotEmpty = $parameters
            ->each(function (ReflectionParameter $reflectionParameter) use ($method) {
                $name = $reflectionParameter->getName();
                $value = $this->valueFactory->make($method->getTestClass(), $reflectionParameter->getType());

                $method->addStatement(
                    $this->statementFactory->makeAffect($name, $value, false)
                );
            });

        if ($parametersNotEmpty) {
            $method->addStatement(new TestStatement(''));
        }
    }

    /**
     * Retrieve the parameters as they will be added in method argument.
     *
     * @param Collection $parameters
     *
     * @return string
     */
    protected function getParametersString(Collection $parameters): string
    {
        $parameters = $parameters
            ->map(function (ReflectionParameter $reflectionParameter) {
                return '$'.$reflectionParameter->getName();
            });

        $parametersString = $parameters->implode(', ');

        if ($parameters->count() > 1) {
            return '['.$parametersString.']';
        }

        return $parametersString;
    }
}
