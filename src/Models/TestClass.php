<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\Concerns\HasTestDocumentation;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tightenco\Collect\Support\Collection;

/**
 * Class TestClass.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestClass implements Renderable
{
    use HasTestDocumentation;

    /**
     * @var ReflectionClass The class for which this test class is created.
     */
    protected $reflectionClass;

    /**
     * @var string The complete name of the class (including namespace).
     */
    protected $name;

    /**
     * @var TestImport[]|Collection The list of test imports.
     */
    protected $imports;

    /**
     * @var TestTrait[]|Collection The list of test traits.
     */
    protected $traits;

    /**
     * @var TestProperty[]|Collection The list of test properties.
     */
    protected $properties;

    /**
     * @var TestMethod[]|Collection The list of test methods.
     */
    protected $methods;

    /**
     * TestClass constructor.
     *
     * @param ReflectionClass $reflectionClass
     * @param string          $name
     */
    public function __construct(ReflectionClass $reflectionClass, string $name)
    {
        $this->reflectionClass = $reflectionClass;
        $this->name = $name;

        $this->imports = new Collection();
        $this->traits = new Collection();
        $this->properties = new Collection();
        $this->methods = new Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Renderer $renderer): Renderer
    {
        return $renderer->visitTestClass($this);
    }

    /**
     * @return ReflectionClass
     */
    public function getReflectionClass(): ReflectionClass
    {
        return $this->reflectionClass;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getNamespace(): ?string
    {
        if (! Str::contains('\\', $this->name)) {
            return null;
        }

        return Str::beforeLast('\\', $this->name);
    }

    /**
     * @return string
     */
    public function getShortName(): string
    {
        return Str::afterLast('\\', $this->name);
    }

    /**
     * @return TestImport[]|Collection
     */
    public function getImports(): Collection
    {
        return $this->imports;
    }

    /**
     * @param TestImport $import
     *
     * @return static
     */
    public function addImport(TestImport $import): self
    {
        $this->imports->add($import->setTestClass($this));

        return $this;
    }

    /**
     * @return TestTrait[]|Collection
     */
    public function getTraits(): Collection
    {
        return $this->traits;
    }

    /**
     * @param TestTrait $trait
     *
     * @return static
     */
    public function addTrait(TestTrait $trait): self
    {
        $this->traits->add($trait->setTestClass($this));

        return $this;
    }

    /**
     * @return TestProperty[]|Collection
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param TestProperty $property
     *
     * @return static
     */
    public function addProperty(TestProperty $property): self
    {
        $this->properties->add($property->setTestClass($this));

        return $this;
    }

    /**
     * @return TestMethod[]|Collection
     */
    public function getMethods(): Collection
    {
        return $this->methods;
    }

    /**
     * @param TestMethod $method
     *
     * @return static
     */
    public function addMethod(TestMethod $method): self
    {
        $this->methods->add($method->setTestClass($this));

        return $this;
    }
}
