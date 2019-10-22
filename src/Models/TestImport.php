<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\Concerns\HasTestClassParent;

/**
 * Class TestImport.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestImport implements Renderable
{
    use HasTestClassParent;

    /**
     * @var string The complete name of the class (including namespace).
     */
    protected $name;

    /**
     * @var string|null The alias of this import.
     */
    protected $alias;

    /**
     * TestImport constructor.
     *
     * @param string      $name
     * @param string|null $alias
     */
    public function __construct(string $name, ?string $alias = null)
    {
        $this->name = $name;
        $this->alias = $alias;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Renderer $renderer): Renderer
    {
        return $renderer->visitTestImport($this);
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

    /**
     * @return string
     */
    public function getFinalName(): string
    {
        return $this->alias ?? Str::afterLast('\\', $this->name);
    }
}
