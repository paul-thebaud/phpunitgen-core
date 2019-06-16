<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Mocks;

use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
use PhpUnitGen\Core\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionParameter;

/**
 * Class AbstractMockGenerator.
 *
 * The mock generator for Mockery.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
abstract class AbstractMockGenerator implements MockGenerator
{
    /**
     * @var ImportFactory
     */
    protected $importFactory;

    public function __construct(ImportFactory $importFactory)
    {
        $this->importFactory = $importFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function generateForParameter(TestMethod $method, ReflectionParameter $parameter): void
    {
        $class = $method->getTestClass();

        $mockStatement = $this->generateStatement($class, $parameter);

        if (! $mockStatement) {
            return;
        }

        $propertyName = $parameter->getName().'Mock';

        $mockStatement->prepend("\$this->{$propertyName} = ", 0);
        $method->addStatement($mockStatement);

        // Create the property and document it.
        $propertyType = $this->importFactory->create($class, $this->getMockClass());
        $realType = $this->importFactory->create($method->getTestClass(), (string) $parameter->getType());
        $property = new TestProperty($propertyName);
        $property->setDocumentation(
            new TestDocumentation("@var {$propertyType->getFinalName()}|{$realType->getFinalName()}")
        );
        $class->addProperty($property);
    }

    /**
     * {@inheritdoc}
     */
    public function generateStatement(TestClass $class, ReflectionParameter $parameter): ?TestStatement
    {
        if (! $this->canParameterBeMocked($parameter)) {
            return null;
        }

        return $this->mockCreationStatement($class, (string) $parameter->getType());
    }

    /**
     * Check if the given parameter can be mocked.
     *
     * @param ReflectionParameter $parameter
     *
     * @return bool
     */
    protected function canParameterBeMocked(ReflectionParameter $parameter): bool
    {
        return $parameter->getType() && ! $parameter->getType()->isBuiltin();
    }

    /**
     * Get the Mock complete class name for property documentation and importation.
     *
     * @return string
     */
    abstract protected function getMockClass(): string;

    /**
     * Get the mock creation statement for the given test class and reflection parameter.
     *
     * @param TestClass $class
     * @param string    $type
     *
     * @return TestStatement
     */
    abstract protected function mockCreationStatement(TestClass $class, string $type): TestStatement;
}
