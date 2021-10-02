<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Rule;

use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Concerns\ChecksMethods;
use PhpUnitGen\Core\Generators\Tests\Concerns\MocksParameters;
use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Class RuleMethodFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class RuleMethodFactory extends BasicMethodFactory
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

        if (! $this->isMethod($reflectionMethod, 'passes')) {
            throw new InvalidArgumentException(
                "cannot generate tests for method {$reflectionMethod->getShortName()}, not a \"passes\" method"
            );
        }

        $this->makeRuleTestMethod($class, $reflectionMethod, 'WhenOk', 'True', 'valid value');
        $this->makeRuleTestMethod($class, $reflectionMethod, 'WhenFailed', 'False', 'invalid value');
    }

    /**
     * Make a test method which call the "passes" method.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     * @param string           $suffix
     * @param string           $assert
     * @param string           $attributeValue
     */
    protected function makeRuleTestMethod(
        TestClass $class,
        ReflectionMethod $reflectionMethod,
        string $suffix,
        string $assert,
        string $attributeValue
    ): void {
        $method = $this->makeEmpty($reflectionMethod, $suffix);
        $class->addMethod($method);

        $instanceName = $this->getPropertyName($class->getReflectionClass());
        $method->addStatement($this->statementFactory->makeTodo('This test is incomplete.'));
        $method->addStatement(
            $this->statementFactory->makeAssert(
                $assert,
                "\$this->{$instanceName}->passes('attribute', '{$attributeValue}')"
            )
        );
    }
}
