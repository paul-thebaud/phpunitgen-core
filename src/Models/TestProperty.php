<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\Concerns\HasTestClassParent;
use PhpUnitGen\Core\Models\Concerns\HasTestDocumentation;
use PhpUnitGen\Core\Models\Concerns\HasType;

/**
 * Class TestProperty.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestProperty implements Renderable
{
    use HasTestClassParent;
    use HasTestDocumentation;
    use HasType;

    /**
     * @var string The name of the property.
     */
    protected $name;

    /**
     * TestProperty constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Renderer $renderer): Renderer
    {
        return $renderer->visitTestProperty($this);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
