<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests;

use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\ImportFactory;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
use PhpUnitGen\Core\Contracts\Generators\ValueFactory;
use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Roave\BetterReflection\Reflection\ReflectionType;
use Tightenco\Collect\Support\Collection;

/**
 * Class BasicTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class BasicTestGenerator extends AbstractTestGenerator
{
    /**
     * @var MockGenerator
     */
    protected $mockGenerator;

    /**
     * @var ImportFactory
     */
    protected $importFactory;

    /**
     * @var ValueFactory
     */
    protected $valueFactory;

    /**
     * BasicTestGenerator constructor.
     *
     * @param Config        $config
     * @param MockGenerator $mockGenerator
     * @param ImportFactory $importFactory
     * @param ValueFactory  $valueFactory
     */
    public function __construct(
        Config $config,
        MockGenerator $mockGenerator,
        ImportFactory $importFactory,
        ValueFactory $valueFactory
    ) {
        parent::__construct($config);

        $this->mockGenerator = $mockGenerator;
        $this->importFactory = $importFactory;
        $this->valueFactory = $valueFactory;
    }

    /*
     |--------------------------------------------------------------------------
     | "setUp" method creation.
     |--------------------------------------------------------------------------
     */

    /**
     * {@inheritdoc}
     */
    protected function addSetUpTestMethod(TestClass $class): void
    {
        $method = $this->createBaseSetUpMethod($class);

        $constructor = Reflect::method($class->getReflectionClass(), '__construct');
        if (! $constructor || ! $constructor->isPublic() || $constructor->isAbstract()) {
            $property = $this->addClassProperty($class);

            $method->addStatement(new TestStatement('/** @todo Instantiate tested object to test it. */'));
            $method->addStatement(new TestStatement('$this->'.$property->getName().' = null;'));

            return;
        }

        $this->addConstructorParameters($method);
        $this->addClassInstantiation($method);
    }

    /**
     * Create the "setUp" method without any body except parent call.
     *
     * @param TestClass $class
     *
     * @return TestMethod
     */
    protected function createBaseSetUpMethod(TestClass $class): TestMethod
    {
        $method = new TestMethod('setUp', TestMethod::VISIBILITY_PROTECTED);
        $class->addMethod($method);

        $method->setDocumentation(new TestDocumentation('{@inheritdoc}'));
        $method->addStatement(new TestStatement('parent::setUp();'));
        $method->addStatement(new TestStatement(''));

        return $method;
    }

    /**
     * Add the properties and instantiations for the constructor parameters in the given method.
     *
     * @param TestMethod $method
     */
    private function addConstructorParameters(TestMethod $method): void
    {
        $this->getConstructorParameters($method->getTestClass()->getReflectionClass())
            ->each(function (ReflectionParameter $reflectionParameter) use ($method) {
                $this->addConstructorParameter($method, $reflectionParameter);
            });
    }

    /**
     * Add the property and instantiation for the given reflection parameter.
     *
     * @param TestMethod          $method
     * @param ReflectionParameter $reflectionParameter
     */
    private function addConstructorParameter(TestMethod $method, ReflectionParameter $reflectionParameter): void
    {
        $class = $method->getTestClass();
        $type = $reflectionParameter->getType();

        $property = new TestProperty($reflectionParameter->getName());
        $class->addProperty($property);

        $documentation = new TestDocumentation('@var ');
        $property->setDocumentation($documentation);

        if (! $type) {
            $documentation->append('mixed');
        } elseif (! $type->isBuiltin() || in_array((string) $type, ['parent', 'self'])) {
            $documentation->append($this->mockGenerator->getMockType($class)->getFinalName());
        } else {
            $documentation->append((string) $type);
        }

        $statement = new TestStatement(
            "\$this->{$property->getName()} = {$this->valueFactory->create($class, $type)};"
        );

        $method->addStatement($statement);
    }

    /**
     * Get the constructor parameters.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return Collection
     */
    private function getConstructorParameters(ReflectionClass $reflectionClass): Collection
    {
        $constructor = Reflect::method($reflectionClass, '__construct');

        if (! $constructor) {
            return new Collection();
        }

        return Reflect::parameters($constructor);
    }

    /**
     * Add the class instantiation with parameters for class, abstract class or trait.
     *
     * @param TestMethod $method
     */
    private function addClassInstantiation(TestMethod $method): void
    {
        $class = $method->getTestClass();
        $reflectionClass = $class->getReflectionClass();

        $property = $this->addClassProperty($class);

        $statement = new TestStatement('$this->'.$property->getName().' = ');
        $method->addStatement($statement);

        $parametersString = $this->getConstructorParameters($reflectionClass)
            ->map(function (ReflectionParameter $reflectionParameter) {
                return '$this->'.$reflectionParameter->getName();
            })
            ->implode(', ');

        if (! $reflectionClass->isAbstract() && ! $reflectionClass->isTrait()) {
            $statement->append("new {$reflectionClass->getShortName()}({$parametersString});");

            return;
        }

        $statement->append("\$this->getMockBuilder({$reflectionClass->getShortName()}::class)")
            ->addLine("->setConstructorArgs([{$parametersString}])");

        if ($reflectionClass->isAbstract()) {
            $statement->addLine('->getMockForAbstractClass();');

            return;
        }

        $statement->addLine('->getMockForTrait();');
    }

    /**
     * Add the tested class instance property and return it.
     *
     * @param TestClass $class
     *
     * @return TestProperty
     */
    private function addClassProperty(TestClass $class): TestProperty
    {
        $reflectionClass = $class->getReflectionClass();

        $property = new TestProperty(lcfirst($reflectionClass->getShortName()));
        $class->addProperty($property);
        $property->setDocumentation(new TestDocumentation("@var {$reflectionClass->getShortName()}"));

        return $property;
    }

    /*
     |--------------------------------------------------------------------------
     | Test methods.
     |--------------------------------------------------------------------------
     */

    /**
     * {@inheritdoc}
     */
    protected function isTestable(ReflectionMethod $reflectionMethod): bool
    {
        return $this->isGetterOrSetter($reflectionMethod);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleTestableMethod(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        $this->handleGetterOrSetterMethod($class, $reflectionMethod);
    }

    /**
     * Handle a getter or setter method to generate tests for it.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     */
    protected function handleGetterOrSetterMethod(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        if ($this->isGetter($reflectionMethod)) {
            $this->handleGetterMethod($class, $reflectionMethod);

            return;
        }

        $this->handleSetterMethod($class, $reflectionMethod);
    }

    /**
     * Check if the given method is a getter or a setter.
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     */
    protected function isGetterOrSetter(ReflectionMethod $reflectionMethod): bool
    {
        return $this->isGetter($reflectionMethod) || $this->isSetter($reflectionMethod);
    }

    /**
     * Check if the given method is a getter (begins with "get" and has a corresponding property).
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     */
    protected function isGetter(ReflectionMethod $reflectionMethod): bool
    {
        return $this->getPropertyFromMethod($reflectionMethod, 'get') !== null;
    }

    /**
     * Check if the given method is a setter (begins with "set" and has a corresponding property).
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     */
    protected function isSetter(ReflectionMethod $reflectionMethod): bool
    {
        return $this->getPropertyFromMethod($reflectionMethod, 'set') !== null;
    }

    /**
     * Handle a getter method to generate tests for it.
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     */
    protected function handleGetterMethod(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        $method = $this->handlePropertyMethod($class, $reflectionMethod, $reflectionMethod->getReturnType(), 'get');

        [$callTarget, $actualValueTarget] = $this->getCallTargetAndValueTarget($class, $reflectionMethod);

        $method->addStatement(
            new TestStatement('$property->setValue('.$actualValueTarget.', $expected);')
        );
        $method->addStatement(
            new TestStatement('$this->assertSame($expected, '.$callTarget.$reflectionMethod->getShortName().'());')
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
        $type = $reflectionParameter ? $reflectionParameter->getType() : null;

        $method = $this->handlePropertyMethod($class, $reflectionMethod, $type, 'set');

        [$callTarget, $actualValueTarget] = $this->getCallTargetAndValueTarget($class, $reflectionMethod);

        $method->addStatement(
            new TestStatement($callTarget.$reflectionMethod->getShortName().'($expected);')
        );
        $method->addStatement(
            new TestStatement('$this->assertSame($expected, $property->getValue('.$actualValueTarget.'));')
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
     * Get the property for the given method by removing prefix from the method name.
     *
     * @param ReflectionMethod $reflectionMethod
     * @param string           $prefix
     *
     * @return ReflectionProperty|null
     */
    private function getPropertyFromMethod(ReflectionMethod $reflectionMethod, string $prefix): ?ReflectionProperty
    {
        $methodName = $reflectionMethod->getShortName();

        if (! Str::startsWith($prefix, $methodName)) {
            return null;
        }

        return Reflect::property(
            $reflectionMethod->getDeclaringClass(),
            lcfirst(Str::replaceFirst($prefix, '', $methodName))
        );
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
        $method = new TestMethod($this->getTestMethodName($reflectionMethod));
        $class->addMethod($method);

        $reflectionProperty = $this->getPropertyFromMethod($reflectionMethod, $prefix);

        // Expected value variable creation.
        $expectedStatement = (new TestStatement('$expected = '))
            ->append($this->valueFactory->create($method->getTestClass(), $reflectionType))
            ->append(';');
        $method->addStatement($expectedStatement);

        // Reflected property variable creation.
        $reflectionClassImport = $this->importFactory->create($method->getTestClass(), 'ReflectionClass');
        $propertyStatement = (new TestStatement('$property = (new '))
            ->append($reflectionClassImport->getFinalName())
            ->append('(')
            ->append($class->getReflectionClass()->getShortName())
            ->append('::class))')
            ->addLine('->getProperty(\'')
            ->append($reflectionProperty->getName())
            ->append('\');');
        $method->addStatement($propertyStatement);

        if (! $reflectionProperty->isPublic()) {
            $method->addStatement(new TestStatement('$property->setAccessible(true);'));
        }

        return $method;
    }
}
