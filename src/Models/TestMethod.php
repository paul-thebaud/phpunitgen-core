<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\Concerns\HasTestClassParent;
use PhpUnitGen\Core\Models\Concerns\HasTestDocumentation;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Tightenco\Collect\Support\Collection;

/**
 * Class TestMethod.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestMethod implements Renderable
{
    use HasTestClassParent;
    use HasTestDocumentation;

    /**
     * The public visibility.
     */
    public const VISIBILITY_PUBLIC = 'public';

    /**
     * The protected visibility.
     */
    public const VISIBILITY_PROTECTED = 'protected';

    /**
     * The private visibility.
     */
    public const VISIBILITY_PRIVATE = 'private';

    /**
     * @var ReflectionMethod|null The method for which this test method is created.
     */
    protected $reflectionMethod;

    /**
     * @var string The name of the method.
     */
    protected $name;

    /**
     * @var string The visibility of the method (public, protected or private).
     */
    protected $visibility;

    /**
     * @var TestProvider|null The data provider.
     */
    protected $provider;

    /**
     * @var TestParameter[]|Collection The list of parameters.
     */
    protected $parameters;

    /**
     * @var TestStatement[]|Collection The list of statements.
     */
    protected $statements;

    /**
     * TestMethod constructor.
     *
     * @param string $name
     * @param string $visibility
     */
    public function __construct(string $name, string $visibility = self::VISIBILITY_PUBLIC)
    {
        $this->name = $name;
        $this->visibility = $visibility;

        $this->parameters = new Collection();
        $this->statements = new Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Renderer $renderer): void
    {
        $renderer->visitTestMethod($this);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @return TestProvider|null
     */
    public function getProvider(): ?TestProvider
    {
        return $this->provider;
    }

    /**
     * @param TestProvider|null $provider
     *
     * @return static
     */
    public function setProvider(?TestProvider $provider): self
    {
        $this->provider = $provider ? $provider->setTestMethod($this) : null;

        return $this;
    }

    /**
     * @return TestParameter[]|Collection
     */
    public function getParameters(): Collection
    {
        return $this->parameters;
    }

    /**
     * @param TestParameter $parameter
     *
     * @return static
     */
    public function addParameter(TestParameter $parameter): self
    {
        $this->parameters->add($parameter->setTestMethod($this));

        return $this;
    }

    /**
     * @return TestStatement[]|Collection
     */
    public function getStatements(): Collection
    {
        return $this->statements;
    }

    /**
     * @param TestStatement $statement
     *
     * @return static
     */
    public function addStatement(TestStatement $statement): self
    {
        $this->statements->add($statement->setTestMethod($this));

        return $this;
    }
}
