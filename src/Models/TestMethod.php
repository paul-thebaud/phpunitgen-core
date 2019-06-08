<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\Concerns\HasTestClassParent;
use Tightenco\Collect\Support\Collection;

/**
 * Class TestMethod.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestMethod implements Renderable
{
    use HasTestClassParent;

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
     * @var string $name The name of the method.
     */
    protected $name;

    /**
     * @var string $visibility The visibility of the method (public, protected or private).
     */
    protected $visibility;

    /**
     * @var TestProvider|null $provider The data provider.
     */
    protected $provider;

    /**
     * @var TestParameter[]|Collection $parameters The list of parameters.
     */
    protected $parameters;

    /**
     * @var TestStatement[]|Collection $statements The list of statements.
     */
    protected $statements;

    /**
     * TestMethod constructor.
     *
     * @param TestClass $testClass
     * @param string    $name
     * @param string    $visibility
     */
    public function __construct(
        TestClass $testClass,
        string $name,
        string $visibility = self::VISIBILITY_PUBLIC
    ) {
        $this->testClass = $testClass->addMethod($this);

        $this->name       = $name;
        $this->visibility = $visibility;

        $this->parameters = new Collection();
        $this->statements = new Collection();
    }

    /**
     * {@inheritDoc}
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
        $this->provider = $provider;

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
        $this->parameters->add($parameter);

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
        $this->statements->add($statement);

        return $this;
    }
}
