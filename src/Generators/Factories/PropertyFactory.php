<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Factories;

use PHPStan\BetterReflection\Reflection\ReflectionParameter;
use PhpUnitGen\Core\Aware\DocumentationFactoryAwareTrait;
use PhpUnitGen\Core\Aware\ImportFactoryAwareTrait;
use PhpUnitGen\Core\Aware\MockGeneratorAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\DocumentationFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\ImportFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\MockGeneratorAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\PropertyFactory as PropertyFactoryContract;
use PhpUnitGen\Core\Generators\Concerns\InstantiatesClass;
use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestProperty;
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

        $typeHint = $this->makeDocTypeFromString($class, $type, $isBuiltIn);
        if ($isMock && $typeHint instanceof TestImport) {
            $typeHint = new Collection([$typeHint, $this->mockGenerator->getMockType($class)]);
        }

        $property->setDocumentation(
            $this->documentationFactory->makeForProperty($property, $typeHint)
        );

        return $property;
    }

    /**
     * Get the type hint that will be added in documentation with the string version of type.
     *
     * @param TestClass $class
     * @param string    $type
     * @param bool      $isBuiltIn
     *
     * @return TestImport|string
     */
    protected function makeDocTypeFromString(TestClass $class, string $type, bool $isBuiltIn)
    {
        if (in_array($type, ['parent', 'self'])) {
            return $this->importFactory->make($class, $class->getReflectionClass()->getName());
        }

        if ($type === 'mixed' || $isBuiltIn) {
            return $type;
        }

        return $this->importFactory->make($class, $type);
    }
}
