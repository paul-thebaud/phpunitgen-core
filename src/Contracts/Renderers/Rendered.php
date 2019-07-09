<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Renderers;

/**
 * Interface Rendered.
 *
 * An object which contains the rendered result from a Renderer.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface Rendered
{
    /**
     * Get the rendered source code as a string.
     *
     * @return string
     */
    public function toString(): string;
}
