<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\Concerns\HasTestClassParent;

/**
 * Class TestImport.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestImport implements Renderable
{
    use HasTestClassParent;

    /**
     * @var string $name The complete name of the class (including namespace).
     */
    protected $name;

    /**
     * @var string|null $alias The alias of this import.
     */
    protected $alias;

    /**
     * TestImport constructor.
     *
     * @param TestClass   $testClass
     * @param string      $name
     * @param string|null $alias
     */
    public function __construct(TestClass $testClass, string $name, ?string $alias = null)
    {
        $this->testClass = $testClass->addImport($this);

        $this->name  = $name;
        $this->alias = $alias;
    }

    /**
     * {@inheritDoc}
     */
    public function accept(Renderer $renderer): void
    {
        $renderer->visitTestImport($this);
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
    public function getAlias(): ?string
    {
        return $this->alias;
    }
}