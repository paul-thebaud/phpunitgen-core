<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\Concerns\HasLines;

/**
 * Class TestDocumentation.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestDocumentation implements Renderable
{
    use HasLines;

    /**
     * TestDocumentation constructor.
     *
     * @param string|null $firstLine
     */
    public function __construct(?string $firstLine = null)
    {
        $this->initializeLines($firstLine);
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Renderer $renderer): Renderer
    {
        return $renderer->visitTestDocumentation($this);
    }
}
