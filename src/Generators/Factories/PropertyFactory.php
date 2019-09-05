<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Factories;

use PhpUnitGen\Core\Aware\DocumentationFactoryAwareTrait;
use PhpUnitGen\Core\Aware\ImportFactoryAwareTrait;
use PhpUnitGen\Core\Aware\MockGeneratorAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\DocumentationFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\ImportFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\MockGeneratorAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\PropertyFactory as PropertyFactoryContract;
use PhpUnitGen\Core\Generators\Concerns\InstantiatesClass;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestProperty;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionType;
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
    DocumentationFactoryAware,
    MockGeneratorAware,
    ImportFactoryAware
{
    use DocumentationFactoryAwareTrait;
    use ImportFactoryAwareTrait;
    use MockGeneratorAwareTrait;
    use InstantiatesClass;

    /**
     * {@inheritdoc}
     */
    public function makeForClass(TestClass $class): TestProperty
    {
        $reflectionClass = $class->getReflectionClass();

        $property = new TestProperty($this->getPropertyName($reflectionClass));

        $import = $this->importFactory->make($class, $reflectionClass->getName());

        $property->setDocumentation(
            $this->documentationFactory->makeForProperty($property, $import)
        );

        return $property;
    }

    /**
     * {@inheritdoc}
     */
    public function makeForParameter(TestClass $class, ReflectionParameter $reflectionParameter): TestProperty
    {
        $property = new TestProperty($reflectionParameter->getName());

        $typeHint = $this->makeDocType($class, $reflectionParameter->getType());
        if ($typeHint instanceof TestImport) {
            $typeHint = new Collection([$typeHint, $this->mockGenerator->getMockType($class)]);
        }

        $property->setDocumentation(
            $this->documentationFactory->makeForProperty($property, $typeHint)
        );

        return $property;
    }

    /**
     * Get the type hint that will be added in documentation.
     *
     * @param TestClass           $class
     * @param ReflectionType|null $reflectionType
     *
     * @return TestImport|string
     */
    protected function makeDocType(TestClass $class, ?ReflectionType $reflectionType)
    {
        $stringType = $reflectionType ? strval($reflectionType) : 'mixed';

        if (in_array($stringType, ['parent', 'self'])) {
            return $this->importFactory->make($class, $class->getReflectionClass()->getName());
        }

        if ($stringType === 'mixed' || $reflectionType->isBuiltin()) {
            return $stringType;
        }

        return $this->importFactory->make($class, $stringType);
    }
}
