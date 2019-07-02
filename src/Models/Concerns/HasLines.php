<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models\Concerns;

use Tightenco\Collect\Support\Collection;

/**
 * Trait HasLines.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait HasLines
{
    /**
     * @var string[]|Collection The statement lines.
     */
    protected $lines;

    /**
     * Initialize the lines collection with or without a first line.
     *
     * @param string|null $firstLine
     */
    protected function initializeLines(?string $firstLine): void
    {
        $this->lines = new Collection();

        if ($firstLine !== null) {
            $this->lines->add($firstLine);
        }
    }

    /**
     * @return Collection
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    /**
     * @param string $content
     *
     * @return static
     */
    public function addLine(string $content = ''): self
    {
        $this->lines->add($content);

        return $this;
    }

    /**
     * @return static
     */
    public function removeLine(): self
    {
        $this->lines->pop();

        return $this;
    }

    /**
     * Prepend content on the last line. Use the n line if $key is defined.
     *
     * @param string   $content
     * @param int|null $key
     *
     * @return static
     */
    public function prepend(string $content, ?int $key = null): self
    {
        if ($key === null) {
            $key = $this->lines->keys()->last();
        }

        $this->lines->put($key, $content.$this->lines->get($key));

        return $this;
    }

    /**
     * Append content on the last line. Use the n line if $key is defined.
     *
     * @param string   $content
     * @param int|null $key
     *
     * @return static
     */
    public function append(string $content, ?int $key = null): self
    {
        if ($key === null) {
            $key = $this->lines->keys()->last();
        }

        $this->lines->put($key, $this->lines->get($key).$content);

        return $this;
    }
}
