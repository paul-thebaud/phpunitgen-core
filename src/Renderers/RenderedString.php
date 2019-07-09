<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Renderers;

use PhpUnitGen\Core\Contracts\Renderers\Rendered;

/**
 * Class RenderedString.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class RenderedString implements Rendered
{
    /**
     * @var string The rendered source code as string.
     */
    protected $rendered;

    /**
     * RenderedString constructor.
     *
     * @param string $rendered
     */
    public function __construct(string $rendered)
    {
        $this->rendered = $rendered;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return $this->rendered;
    }
}
