<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Renderers;

/**
 * Class RenderedLine.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class RenderedLine
{
    /**
     * @var int The line indentation.
     */
    protected $indentation;

    /**
     * @var string The line content.
     */
    protected $content;

    /**
     * RenderedLine constructor.
     *
     * @param int    $indentation
     * @param string $content
     */
    public function __construct(int $indentation, string $content)
    {
        $this->indentation = $indentation;
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * Appends the given content to the current line content.
     *
     * @param string $content
     *
     * @return static
     */
    public function prepend(string $content): self
    {
        $this->content = $content.$this->content;

        return $this;
    }

    /**
     * Appends the given content to the current line content.
     *
     * @param string $content
     *
     * @return static
     */
    public function append(string $content): self
    {
        $this->content .= $content;

        return $this;
    }

    /**
     * Render the line as string with correct indentation.
     *
     * @return string
     */
    public function render(): string
    {
        if ($this->content === '') {
            return '';
        }

        return str_repeat('    ', $this->indentation).$this->content;
    }
}
