<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests;

use phpDocumentor\Reflection\DocBlock\Tag;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Class AbstractTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
abstract class AbstractTestGenerator implements TestGenerator
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * AbstractTestGenerator constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ReflectionClass $reflectionClass): TestClass
    {
        if (! $this->canGenerateFor($reflectionClass)) {
            throw new InvalidArgumentException('cannot generate tests for given reflection class');
        }

        $class = $this->createTestClass($reflectionClass);

        $this->addTestClassDocumentation($class);
        $this->addTestClassImports($class);
        $this->addTestClassTraits($class);
        $this->addTestClassProperties($class);
        $this->addTestClassMethods($class);

        return $class;
    }

    /**
     * {@inheritdoc}
     */
    public function canGenerateFor(ReflectionClass $reflectionClass): bool
    {
        return ! $reflectionClass->isInterface()
            && ! $reflectionClass->isAnonymous();
    }

    /*
     |--------------------------------------------------------------------------
     | Test class hooks.
     |--------------------------------------------------------------------------
     */

    /**
     * Create the base test class object without methods.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return TestClass
     */
    protected function createTestClass(ReflectionClass $reflectionClass): TestClass
    {
        return new TestClass($reflectionClass, $this->getTestClassName($reflectionClass));
    }

    /**
     * Get the test class complete name.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return string
     */
    protected function getTestClassName(ReflectionClass $reflectionClass): string
    {
        $name = $reflectionClass->getName();

        $baseNamespace = $this->config->baseNamespace();
        if ($baseNamespace !== '') {
            $name = Str::replaceFirst($baseNamespace, '', $name);
        }

        return $this->config->baseTestNamespace().$name.'Test';
    }

    /**
     * Add the documentation on the created test class.
     *
     * @param TestClass $class
     */
    protected function addTestClassDocumentation(TestClass $class): void
    {
        $documentation = new TestDocumentation("Class {$class->getShortName()}.");
        $documentation->addLine();

        $hasDocumentation = Reflect::docBlockTags($class->getReflectionClass())
            ->reject(function (Tag $tag) {
                return ! in_array($tag->getName(), $this->config->mergedPhpDoc());
            })
            ->map(function (Tag $tag) {
                return $tag->render();
            })
            ->merge($this->config->phpDoc())
            ->unique()
            ->each(function ($line) use ($documentation) {
                $documentation->addLine($line);
            })
            ->isNotEmpty();

        if ($hasDocumentation) {
            $documentation->addLine();
        }

        $documentation->addLine('@covers '.$class->getReflectionClass()->getName());

        $class->setDocumentation($documentation);
    }

    /**
     * Add the imports on the created test class.
     *
     * @param TestClass $class
     */
    protected function addTestClassImports(TestClass $class): void
    {
        $class->addImport(new TestImport($this->config->testCase()));
        $class->addImport(new TestImport($class->getReflectionClass()->getName()));
    }

    /**
     * Add the traits on the created test class.
     *
     * @param TestClass $class
     */
    protected function addTestClassTraits(TestClass $class): void
    {
    }

    /**
     * Add the properties on the created test class.
     *
     * @param TestClass $class
     */
    protected function addTestClassProperties(TestClass $class): void
    {
    }

    /**
     * Add the properties on the created test class.
     *
     * @param TestClass $class
     */
    protected function addTestClassMethods(TestClass $class): void
    {
        if ($this->shouldAddSetUp($class)) {
            $this->addSetUpTestMethod($class);
            $this->addTearDownTestMethod($class);
        }

        Reflect::methods($class->getReflectionClass())
            ->each(function (ReflectionMethod $reflectionMethod) use ($class) {
                if (! $this->shouldHandleTestMethod($reflectionMethod)) {
                    return;
                }

                if ($this->config->hasAutomaticTests() && $this->isTestable($reflectionMethod)) {
                    $this->handleTestableMethod($class, $reflectionMethod);
                } else {
                    $this->handleNotTestableMethod($class, $reflectionMethod);
                }
            });
    }

    /*
     |--------------------------------------------------------------------------
     | Test methods hooks.
     |--------------------------------------------------------------------------
     */

    /**
     * Check "setUp" and "tearDown" should be created for the given class.
     *
     * @param TestClass $class
     *
     * @return bool
     */
    protected function shouldAddSetUp(TestClass $class): bool
    {
        return $this->config->hasAutomaticTests();
    }

    /**
     * Add the "setUp" method for the given class.
     *
     * @param TestClass $class
     */
    abstract protected function addSetUpTestMethod(TestClass $class): void;

    /**
     * Add the "tearDown" method for the given class.
     *
     * @param TestClass $class
     */
    protected function addTearDownTestMethod(TestClass $class): void
    {
        $method = new TestMethod('tearDown', TestMethod::VISIBILITY_PROTECTED);
        $class->addMethod($method);

        $method->setDocumentation(new TestDocumentation('{@inheritdoc}'));
        $method->addStatement(new TestStatement('parent::tearDown();'));
        $method->addStatement(new TestStatement(''));

        $class->getProperties()
            ->each(function (TestProperty $property) use ($method) {
                $method->addStatement(new TestStatement("unset(\$this->{$property->getName()});"));
            });
    }

    /**
     * Check if the given reflection method should have generated tests.
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     */
    protected function shouldHandleTestMethod(ReflectionMethod $reflectionMethod): bool
    {
        return $reflectionMethod->isPublic()
            && ! Str::containsRegex($this->config->excludedMethods(), $reflectionMethod->getShortName());
    }

    /**
     * Check if the given reflection method can receive automatically generated tests or not.
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     */
    abstract protected function isTestable(ReflectionMethod $reflectionMethod): bool;

    /**
     * Handle a method for which tests can be automatically generated.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     */
    abstract protected function handleTestableMethod(TestClass $class, ReflectionMethod $reflectionMethod): void;

    /**
     * Handle a method for which tests can not be automatically generated.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     */
    protected function handleNotTestableMethod(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        $method = new TestMethod($this->getTestMethodName($reflectionMethod));
        $class->addMethod($method);

        $method->addStatement(new TestStatement('/** @todo This test is incomplete. */'));
        $method->addStatement(new TestStatement('$this->markTestIncomplete();'));
    }

    /**
     * Get the test method name.
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return string
     */
    protected function getTestMethodName(ReflectionMethod $reflectionMethod): string
    {
        return 'test'.ucfirst($reflectionMethod->getShortName());
    }
}
