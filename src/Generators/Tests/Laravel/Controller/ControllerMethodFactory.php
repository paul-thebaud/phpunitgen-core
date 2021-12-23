<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Controller;

use PhpUnitGen\Core\Generators\Tests\Laravel\Command\CommandMethodFactory;
use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Class ControllerMethodFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class ControllerMethodFactory extends CommandMethodFactory
{
    /**
     * The mapping between HTTP method to tests and strings controller methods should starts with.
     */
    protected const HTTP_METHODS_MAP = [
        'post'   => 'store',
        'put'    => 'update',
        'delete' => ['delete', 'destroy'],
    ];

    /**
     * {@inheritdoc}
     */
    public function makeTestable(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        if ($this->isGetterOrSetter($reflectionMethod)) {
            parent::makeTestable($class, $reflectionMethod);

            return;
        }

        $methodToUse = $this->resolveTestMethod($class, $reflectionMethod);

        $method = $this->makeEmpty($reflectionMethod);

        $method->addStatement($this->statementFactory->makeTodo('This test is incomplete.'));
        $method->addStatement(
            (new TestStatement('$this->'))
                ->append($methodToUse)
                ->append('(\'/path\'')
                ->append(Str::startsWith(['get', 'delete'], $methodToUse) ? '' : ', [ /* data */ ]')
                ->append(')')
                ->addLine('->assertStatus(200)')
        );

        $class->addMethod($method);
    }

    /**
     * Resolve the test method to call in generated test, based on the class namespace and method name.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     *
     * @return string
     */
    protected function resolveTestMethod(TestClass $class, ReflectionMethod $reflectionMethod): string
    {
        $httpMethodToTest = $this->resolveHttpMethodToTest($reflectionMethod);

        if (Str::containsRegex('\\\\api\\\\', $class->getReflectionClass()->getName())) {
            return $httpMethodToTest.'Json';
        }

        return $httpMethodToTest;
    }

    /**
     * Resolve the HTTP method to use in generated test from method name.
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return string
     */
    protected function resolveHttpMethodToTest(ReflectionMethod $reflectionMethod): string
    {
        $reflectionMethodName = $reflectionMethod->getShortName();

        foreach (self::HTTP_METHODS_MAP as $httpMethod => $search) {
            if (Str::startsWith($search, $reflectionMethodName)) {
                return $httpMethod;
            }
        }

        return 'get';
    }
}
