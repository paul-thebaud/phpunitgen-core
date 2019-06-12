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
 * Class MockeryMockGenerator.
 *
 * The mock generator for Mockery.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class MockeryMockGenerator implements MockGenerator
{
    use CreatesTestImports;

    /**
     * {@inheritdoc}
     */
    public function generateProperty(TestClass $class, ReflectionParameter $parameter): void
    {
        $type = $parameter->getType();
        if (! $type || $type->isBuiltin()) {
            return;
        }

        new TestProperty(
            $class,
            $parameter->getName().'Mock',
            $this->createTestImport($class, 'Mockery\\Mock')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generateStatement(TestMethod $method, ReflectionParameter $parameter): void
    {
        $type = $parameter->getType();
        if (! $type || $type->isBuiltin()) {
            return;
        }

        $mockeryImport = $this->createTestImport($method->getTestClass(), 'Mockery');
        $classImport = $this->createTestImport($method->getTestClass(), (string) $type);

        new TestStatement(
            $method,
            "\$this->{$parameter->getName()}Mock = {$mockeryImport}::mock({$classImport}::class);"
        );
    }
}
