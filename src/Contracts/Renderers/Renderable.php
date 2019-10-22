<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Renderers;

/**
 * Interface Renderable.
 *
 * An object which can be visited and rendered as generated test code by Renderer implementation.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface Renderable
{
    /**
     * Accept a renderer to visit render this object.
     *
     * @param Renderer $renderer
     *
     * @return Renderer
     */
    public function accept(Renderer $renderer): Renderer;
}
