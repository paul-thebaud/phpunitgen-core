<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Factories;

use PhpUnitGen\Core\Aware\ConfigAwareTrait;
use PhpUnitGen\Core\Aware\DocumentationFactoryAwareTrait;
use PhpUnitGen\Core\Aware\MockGeneratorAwareTrait;
use PhpUnitGen\Core\Aware\TypeFactoryAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\ConfigAware;
use PhpUnitGen\Core\Contracts\Aware\DocumentationFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\MockGeneratorAware;
use PhpUnitGen\Core\Contracts\Aware\TypeFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\PropertyFactory as PropertyFactoryContract;
use PhpUnitGen\Core\Generators\Concerns\InstantiatesClass;
use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestProperty;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Tightenco\Collect\Support\Collection;

/**
 * Class PropertyFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class PropertyFactory implements
    PropertyFactoryContract,
    ConfigAware,
    DocumentationFactoryAware,
    MockGeneratorAware,
    TypeFactoryAware
{
    use ConfigAwareTrait;
    use DocumentationFactoryAwareTrait;
    use MockGeneratorAwareTrait;
    use TypeFactoryAwareTrait;
    use InstantiatesClass;

    /**
     * {@inheritdoc}
     */
    public function makeForClass(TestClass $class): TestProperty
    {
        $reflectionClass = $class->getReflectionClass();

        return $this->makeCustom(
            $class,
            $this->getPropertyName($reflectionClass),
            $reflectionClass->getName(),
            false,
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    public function makeForParameter(TestClass $class, ReflectionParameter $reflectionParameter): TestProperty
    {
        $stringType = 'mixed';
        $isBuiltIn = true;

        $reflectionType = Reflect::parameterType($reflectionParameter);
        if ($reflectionType) {
            $stringType = $reflectionType->getType();
            $isBuiltIn = $reflectionType->isBuiltin();
        }

        return $this->makeCustom($class, $reflectionParameter->getName(), $stringType, $isBuiltIn);
    }

    /**
     * {@inheritdoc}
     */
    public function makeCustom(
        TestClass $class,
        string $name,
        string $type,
        bool $isBuiltIn = false,
        bool $isMock = true
    ): TestProperty {
        $property = new TestProperty($name);

        $type = $this->typeFactory->makeFromString($class, $type, $isBuiltIn);
        $types = $isMock && $type instanceof TestImport
            ? new Collection([$type, $this->mockGenerator->getMockType($class)])
            : new Collection([$type]);

        $this->typeOrDocumentProperty($property, $types);

        return $property;
    }

    /**
     * Add a type or a documentation to property depending on configuration.
     *
     * @param TestProperty $property
     * @param Collection   $types
     */
    protected function typeOrDocumentProperty(TestProperty $property, Collection $types): void
    {
        if ($this->config->testClassTypedProperties()) {
            $property->setType($this->typeFactory->formatTypes($types));
        } else {
            $property->setDocumentation(
                $this->documentationFactory->makeForProperty($property, $types)
            );
        }
    }
}
