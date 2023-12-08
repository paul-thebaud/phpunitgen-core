<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Basic;

use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Factories\MethodFactory;
use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestStatement;
use PhpUnitGen\Core\Reflection\ReflectionType;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;

/**
 * Class BasicMethodFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class BasicMethodFactory extends MethodFactory
{
    use ManagesGetterAndSetter;

    /**
     * {@inheritdoc}
     */
    public function makeTestable(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        if ($this->isGetter($reflectionMethod)) {
            $this->handleGetterMethod($class, $reflectionMethod);

            return;
        }

        if ($this->isSetter($reflectionMethod)) {
            $this->handleSetterMethod($class, $reflectionMethod);

            return;
        }

        throw new InvalidArgumentException(
            "cannot generate tests for method {$reflectionMethod->getShortName()}, not a getter or a setter"
        );
    }

    /**
     * Handle a getter method to generate tests for it.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     */
    protected function handleGetterMethod(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        $method = $this->handlePropertyMethod($class, $reflectionMethod, Reflect::returnType($reflectionMethod), 'get');

        [$callTarget, $actualValueTarget] = $this->getCallTargetAndValueTarget($class, $reflectionMethod);

        $method->addStatement(
            new TestStatement('$property->setValue('.$actualValueTarget.', $expected)')
        );
        $method->addStatement(
            $this->statementFactory->makeAssert('same', '$expected', $callTarget.$reflectionMethod->getShortName().'()')
        );
    }

    /**
     * Handle a getter method to generate tests for it.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     */
    protected function handleSetterMethod(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        /** @var ReflectionParameter|null $reflectionParameter */
        $reflectionParameter = Reflect::parameters($reflectionMethod)->first();
        $type = $reflectionParameter ? Reflect::parameterType($reflectionParameter) : null;

        $method = $this->handlePropertyMethod($class, $reflectionMethod, $type, 'set');

        [$callTarget, $actualValueTarget] = $this->getCallTargetAndValueTarget($class, $reflectionMethod);

        $method->addStatement(
            new TestStatement($callTarget.$reflectionMethod->getShortName().'($expected)')
        );
        $method->addStatement(
            $this->statementFactory->makeAssert('same', '$expected', '$property->getValue('.$actualValueTarget.')')
        );
    }

    /**
     * Get the target to call the tested method and the target to define or get the value to test.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     *
     * @return array
     */
    private function getCallTargetAndValueTarget(TestClass $class, ReflectionMethod $reflectionMethod): array
    {
        $className = $class->getReflectionClass()->getShortName();

        if ($reflectionMethod->isStatic()) {
            $callTarget = $class->getReflectionClass()->getShortName().'::';
            $actualValueTarget = 'null';
        } else {
            $callTarget = '$this->'.lcfirst($className).'->';
            $actualValueTarget = '$this->'.lcfirst($className);
        }

        return [$callTarget, $actualValueTarget];
    }

    /**
     * Handle a method for a property to create expected value and reflection property instantiation.
     *
     * @param TestClass           $class
     * @param ReflectionMethod    $reflectionMethod
     * @param ReflectionType|null $reflectionType
     * @param string              $prefix
     *
     * @return TestMethod
     */
    private function handlePropertyMethod(
        TestClass $class,
        ReflectionMethod $reflectionMethod,
        ?ReflectionType $reflectionType,
        string $prefix
    ): TestMethod {
        $method = $this->makeEmpty($reflectionMethod);
        $class->addMethod($method);

        $reflectionProperty = $this->getPropertyFromMethod($reflectionMethod, $prefix);

        // Expected value variable creation.
        $expectedStatement = (new TestStatement('$expected = '))
            ->append($this->valueFactory->make($method->getTestClass(), $reflectionType));
        $method->addStatement($expectedStatement);

        // Reflected property variable creation.
        $reflectionClassImport = $this->importFactory->make($method->getTestClass(), 'ReflectionClass');
        $propertyStatement = (new TestStatement('$property = (new '))
            ->append($reflectionClassImport->getFinalName())
            ->append('(')
            ->append($class->getReflectionClass()->getShortName())
            ->append('::class))')
            ->addLine('->getProperty(\'')
            ->append($reflectionProperty->getName())
            ->append('\')');
        $method->addStatement($propertyStatement);

        return $method;
    }
}
