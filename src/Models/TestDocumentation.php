<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use Tightenco\Collect\Support\Collection;

/**
 * Class TestDocumentation.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestDocumentation implements Renderable
{
    /**
     * @var string[]|Collection The line of the documentation block.
     */
    protected $lines;

    /**
     * TestDocumentation constructor.
     *
     * @param Collection|null $lines
     */
    public function __construct(Collection $lines = null)
    {
        $this->lines = $lines ?? new Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Renderer $renderer): void
    {
        $renderer->visitTestDocumentation($this);
    }

    /**
     * @return string[]|Collection
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    /**
     * @param string $line
     *
     * @return static
     */
    public function addLine(string $line): self
    {
        $this->lines->add($line);

        return $this;
    }
}
