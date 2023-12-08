<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Factories;

use PhpUnitGen\Core\Aware\DocumentationFactoryAwareTrait;
use PhpUnitGen\Core\Aware\ImportFactoryAwareTrait;
use PhpUnitGen\Core\Aware\StatementFactoryAwareTrait;
use PhpUnitGen\Core\Aware\ValueFactoryAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\DocumentationFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\ImportFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\StatementFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\ValueFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory as MethodFactoryContract;
use PhpUnitGen\Core\Generators\Concerns\InstantiatesClass;
use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Tightenco\Collect\Support\Collection;

/**
 * Class MethodFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class MethodFactory implements
    MethodFactoryContract,
    DocumentationFactoryAware,
    ImportFactoryAware,
    StatementFactoryAware,
    ValueFactoryAware
{
    use DocumentationFactoryAwareTrait;
    use ImportFactoryAwareTrait;
    use StatementFactoryAwareTrait;
    use ValueFactoryAwareTrait;
    use InstantiatesClass;

    /**
     * {@inheritdoc}
     */
    public function makeSetUp(TestClass $class): TestMethod
    {
        $method = new TestMethod('setUp', 'protected');

        $this->makeMethodInherited($method);

        $constructor = $this->getConstructor($class->getReflectionClass());
        if (! $constructor) {
            $this->completeSetUpWithoutConstructor($class, $method);
        } else {
            $this->completeSetUpWithConstructor($class, $method, $constructor);
        }

        return $method;
    }

    /**
     * {@inheritdoc}
     */
    public function makeTearDown(TestClass $class): TestMethod
    {
        $method = new TestMethod('tearDown', 'protected');

        $this->makeMethodInherited($method);

        $properties = $class->getProperties();
        if ($properties->isEmpty()) {
            $method->addStatement(
                $this->statementFactory->makeTodo('Complete the tearDown() method.')
            );

            return $method;
        }

        $properties->each(function (TestProperty $property) use ($method) {
            $method->addStatement(
                new TestStatement("unset(\$this->{$property->getName()})")
            );
        });

        return $method;
    }

    /**
     * {@inheritdoc}
     */
    public function makeEmpty(ReflectionMethod $reflectionMethod, string $suffix = ''): TestMethod
    {
        return new TestMethod(
            $this->makeTestMethodName($reflectionMethod).$suffix
        );
    }

    /**
     * {@inheritdoc}
     */
    public function makeIncomplete(ReflectionMethod $reflectionMethod): TestMethod
    {
        $method = new TestMethod(
            $this->makeTestMethodName($reflectionMethod)
        );

        $method->addStatement($this->statementFactory->makeTodo('This test is incomplete.'));
        $method->addStatement(new TestStatement('self::markTestIncomplete()'));

        return $method;
    }

    /**
     * {@inheritdoc}
     */
    public function makeTestable(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        $class->addMethod($this->makeIncomplete($reflectionMethod));
    }

    /**
     * Get the test method short name.
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return string
     */
    protected function makeTestMethodName(ReflectionMethod $reflectionMethod): string
    {
        return 'test'.ucfirst($reflectionMethod->getShortName());
    }

    /**
     * Add the call to parent method with a new empty line after it.
     *
     * @param TestMethod $method
     */
    protected function makeMethodInherited(TestMethod $method): void
    {
        $method->setDocumentation(
            $this->documentationFactory->makeForInheritedMethod($method)
        );

        $callStatement = (new TestStatement('parent::'))
            ->append($method->getName())
            ->append('()');

        $method->addStatement($callStatement)
            ->addStatement(new TestStatement(''));
    }

    /**
     * Complete the "setUp" method without knowing the constructor.
     *
     * @param TestClass  $class
     * @param TestMethod $method
     */
    protected function completeSetUpWithoutConstructor(TestClass $class, TestMethod $method): void
    {
        $method->addStatement(
            $this->statementFactory->makeTodo('Correctly instantiate tested object to use it.')
        );
        $method->addStatement(
            $this->statementFactory->makeInstantiation($class, new Collection())
        );
    }

    /**
     * Complete the "setUp" method when knowing the constructor.
     *
     * @param TestClass        $class
     * @param TestMethod       $method
     * @param ReflectionMethod $constructor
     */
    protected function completeSetUpWithConstructor(
        TestClass $class,
        TestMethod $method,
        ReflectionMethod $constructor
    ): void {
        $parameters = Reflect::parameters($constructor)
            ->each(function (ReflectionParameter $reflectionParameter) use ($class, $method) {
                $value = $this->valueFactory->make($class, Reflect::parameterType($reflectionParameter));

                $method->addStatement(
                    $this->statementFactory->makeAffect($reflectionParameter->getName(), $value)
                );
            });

        $method->addStatement(
            $this->statementFactory->makeInstantiation($class, $parameters)
        );
    }
}
