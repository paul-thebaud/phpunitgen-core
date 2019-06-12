<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Mocks;

use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
use PhpUnitGen\Core\Generators\Concerns\CreatesTestImports;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionParameter;

/**
 * Class AbstractMockGenerator.
 *
 * The mock generator for Mockery.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
abstract class AbstractMockGenerator implements MockGenerator
{
    use CreatesTestImports;

    /**
     * {@inheritDoc}
     */
    public function generateProperty(TestClass $class, ReflectionParameter $parameter): void
    {
        $type = $parameter->getType();
        if (! $type || $type->isBuiltin()) {
            return;
        }

        new TestProperty(
            $class,
            $parameter->getName() . 'Mock',
            $this->createTestImport($class, $this->getMockClass())
        );
    }

    /**
     * {@inheritDoc}
     */
    public function generateStatement(TestMethod $method, ReflectionParameter $parameter): void
    {
        $type = $parameter->getType();
        if (! $type || $type->isBuiltin()) {
            return;
        }

        $classImport = $this->createTestImport($method->getTestClass(), (string) $type);

        new TestStatement(
            $method,
            "\$this->{$parameter->getName()}Mock = {$this->getMockCreationLine($method->getTestClass(), $classImport)}"
        );
    }

    /**
     * Get the Mock complete class name for property documentation.
     *
     * @return string
     */
    abstract protected function getMockClass(): string;

    /**
     * Get the mock creation line for the given test class and imported class name.
     *
     * @param TestClass $testClass
     * @param string    $class
     *
     * @return string
     */
    abstract protected function getMockCreationLine(TestClass $testClass, string $class): string;
}
