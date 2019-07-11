<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Class PolicyTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class PolicyTestGenerator extends AbstractLaravelTestGenerator
{
    /**
     * {@inheritdoc}
     *
     * "setUp" method should instantiate a property user to test the policy.
     */
    protected function addSetUpTestMethod(TestClass $class): void
    {
        $userClass = $this->config->getOption('laravel.user', 'App\\User');
        $userImport = $this->importFactory->make($class, $userClass);

        $userProperty = new TestProperty('user');
        $userProperty->setDocumentation(new TestDocumentation('@var '.$userImport->getFinalName()));
        $class->addProperty($userProperty);

        $setUpMethod = $this->createBaseSetUpMethod($class);

        $userStatement = new TestStatement("\$this->user = new {$userImport->getFinalName()}();");
        $setUpMethod->addStatement($userStatement);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleNotTestableMethod(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        $this->addPolicyMethod($class, $reflectionMethod, 'False', 'Unauthorized');
        $this->addPolicyMethod($class, $reflectionMethod, 'True', 'Authorized');
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
        $methodName = $reflectionMethod->getShortName();

        $method = new TestMethod('test'.ucfirst($methodName).'When'.$nameSuffix);
        $class->addMethod($method);

        $assertStatement = (new TestStatement('$this->assert'))
            ->append($expected)
            ->append('($this->user->can(\'')
            ->append($methodName)
            ->append('\', null));');

        $method->addStatement(new TestStatement('$this->markTestIncomplete();'));
        $method->addStatement(new TestStatement(''));
        $method->addStatement($assertStatement);
    }
}
