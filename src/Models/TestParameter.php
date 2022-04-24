<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\Concerns\HasTestMethodParent;
use PhpUnitGen\Core\Models\Concerns\HasType;

/**
 * Class TestParameter.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestParameter implements Renderable
{
    use HasTestMethodParent;
    use HasType;

    /**
     * @var string The name of the parameter.
     */
    protected $name;

    /**
     * TestParameter constructor.
     *
     * @param string      $name
     * @param string|null $type
     */
    public function __construct(string $name, ?string $type = null)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Renderer $renderer): Renderer
    {
        return $renderer->visitTestParameter($this);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
