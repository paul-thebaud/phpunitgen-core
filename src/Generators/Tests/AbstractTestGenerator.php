<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests;

use PhpUnitGen\Core\Aware\ClassFactoryAwareTrait;
use PhpUnitGen\Core\Aware\ConfigAwareTrait;
use PhpUnitGen\Core\Aware\ImportFactoryAwareTrait;
use PhpUnitGen\Core\Aware\MethodFactoryAwareTrait;
use PhpUnitGen\Core\Aware\PropertyFactoryAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\ClassFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\ConfigAware;
use PhpUnitGen\Core\Contracts\Aware\ImportFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\MethodFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\PropertyFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\ClassFactory as ClassFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory as DocumentationFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory as ImportFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory as MethodFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\PropertyFactory as PropertyFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory as StatementFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory as ValueFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator as TestGeneratorContract;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Concerns\InstantiatesClass;
use PhpUnitGen\Core\Generators\Factories\ClassFactory;
use PhpUnitGen\Core\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Factories\MethodFactory;
use PhpUnitGen\Core\Generators\Factories\PropertyFactory;
use PhpUnitGen\Core\Generators\Factories\StatementFactory;
use PhpUnitGen\Core\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;

/**
 * Class AbstractTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
abstract class AbstractTestGenerator implements
    TestGenerator,
    ClassFactoryAware,
    ConfigAware,
    ImportFactoryAware,
    MethodFactoryAware,
    PropertyFactoryAware
{
    use ClassFactoryAwareTrait;
    use ConfigAwareTrait;
    use ImportFactoryAwareTrait;
    use MethodFactoryAwareTrait;
    use PropertyFactoryAwareTrait;
    use InstantiatesClass;

    /**
     * {@inheritdoc}
     */
    public static function implementations(): array
    {
        return [
            TestGeneratorContract::class        => static::class,
            ClassFactoryContract::class         => ClassFactory::class,
            DocumentationFactoryContract::class => DocumentationFactory::class,
            ImportFactoryContract::class        => ImportFactory::class,
            MethodFactoryContract::class        => MethodFactory::class,
            PropertyFactoryContract::class      => PropertyFactory::class,
            StatementFactoryContract::class     => StatementFactory::class,
            ValueFactoryContract::class         => ValueFactory::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ReflectionClass $reflectionClass): TestClass
    {
        if (! $this->canGenerateFor($reflectionClass)) {
            throw new InvalidArgumentException(
                'cannot generate tests for given reflection class'
            );
        }

        // First step of a generator is to generate the test class with its
        // documentation, base import (such as TestCase or tested class)
        // and used trait.
        $class = $this->makeClass($reflectionClass);
        $this->addImports($class);
        $this->addTraits($class);

        // Second step is to add the properties that will be used in each test,
        // such as the tested class instance or the mocked dependencies.
        // Those properties should be added only if automatic generation is
        // activated.
        if ($this->shouldAddProperties($class)) {
            $this->addProperties($class);
        }

        // Third step is to add the fixture methods to set up and tear down
        // the properties created on previous step.
        // Those methods should be added only if automatic generation is
        // activated.
        if ($this->shouldAddFixtures($class)) {
            $this->addFixtures($class);
        }

        // Fourth and last step is to add the test methods. We will
        // add incomplete methods (when automation is disable or method should
        // not receive automatic testing) or complex methods with generated
        // tests or parts.
        $this->addMethods($class);

        return $class;
    }

    /**
     * {@inheritdoc}
     */
    public function canGenerateFor(ReflectionClass $reflectionClass): bool
    {
        if ($reflectionClass->isInterface() || $reflectionClass->isAnonymous()) {
            return false;
        }

        $class = $this->makeClass($reflectionClass);

        return Reflect::methods($reflectionClass)
            ->some(function (ReflectionMethod $reflectionMethod) use ($class) {
                return $this->shouldAddMethod($class, $reflectionMethod);
            });
    }

    /*
     |--------------------------------------------------------------------------
     | Test class hooks.
     |--------------------------------------------------------------------------
     */

    /**
     * Check if properties should be added for test class.
     *
     * @param TestClass $class
     *
     * @return bool
     */
    protected function shouldAddProperties(TestClass $class): bool
    {
        return $this->config->automaticGeneration();
    }

    /**
     * Check if fixtures should be added for test class.
     *
     * @param TestClass $class
     *
     * @return bool
     */
    protected function shouldAddFixtures(TestClass $class): bool
    {
        return $this->config->automaticGeneration();
    }

    /**
     * Check if a test method should be added for the given method.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     */
    protected function shouldAddMethod(TestClass $class, ReflectionMethod $reflectionMethod): bool
    {
        return $reflectionMethod->isPublic()
            && ! Str::containsRegex($this->config->excludedMethods(), $reflectionMethod->getShortName());
    }

    /**
     * Make the test class.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return TestClass
     */
    protected function makeClass(ReflectionClass $reflectionClass): TestClass
    {
        return $this->classFactory->make($reflectionClass);
    }

    /**
     * Add the imports on the created test class.
     *
     * @param TestClass $class
     */
    protected function addImports(TestClass $class): void
    {
        $this->importFactory->make($class, $this->config->testCase());
        $this->importFactory->make($class, $class->getReflectionClass()->getName());
    }

    /**
     * Add the traits on the created test class.
     *
     * @param TestClass $class
     */
    protected function addTraits(TestClass $class): void
    {
    }

    /**
     * Add the properties on the created test class.
     *
     * @param TestClass $class
     */
    protected function addProperties(TestClass $class): void
    {
        $class->addProperty($this->propertyFactory->makeForClass($class));

        $constructor = $this->getConstructor($class->getReflectionClass());
        if (! $constructor) {
            return;
        }

        Reflect::parameters($constructor)
            ->each(function (ReflectionParameter $reflectionParameter) use ($class) {
                $class->addProperty(
                    $this->propertyFactory->makeForParameter($class, $reflectionParameter)
                );
            });
    }

    /**
     * Add the fixtures ("setUp" and "tearDown") on the created test class.
     *
     * @param TestClass $class
     */
    protected function addFixtures(TestClass $class): void
    {
        $class->addMethod($this->methodFactory->makeSetUp($class));
        $class->addMethod($this->methodFactory->makeTearDown($class));
    }

    /**
     * Add the methods on the created test class.
     *
     * @param TestClass $class
     */
    protected function addMethods(TestClass $class): void
    {
        Reflect::methods($class->getReflectionClass())
            ->each(function (ReflectionMethod $reflectionMethod) use ($class) {
                if (! $this->shouldAddMethod($class, $reflectionMethod)) {
                    return;
                }

                if ($this->isTestable($class, $reflectionMethod)) {
                    $this->handleForTestable($class, $reflectionMethod);
                } else {
                    $this->handleForNotTestable($class, $reflectionMethod);
                }
            });
    }

    /*
     |--------------------------------------------------------------------------
     | Test methods hooks.
     |--------------------------------------------------------------------------
     */

    /**
     * Check if a non empty test method should be added (method can have automatic generation).
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     */
    abstract protected function isTestable(TestClass $class, ReflectionMethod $reflectionMethod): bool;

    /**
     * Handle the method to generate automatic tests.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     */
    abstract protected function handleForTestable(TestClass $class, ReflectionMethod $reflectionMethod): void;

    /**
     * Handle the method to generate an empty test (using "markTestIsIncomplete").
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     */
    protected function handleForNotTestable(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        $class->addMethod(
            $this->methodFactory->makeIncomplete($reflectionMethod)
        );
    }
}
