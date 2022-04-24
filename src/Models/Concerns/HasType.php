<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models\Concerns;

/**
 * Trait HasType.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait HasType
{
    /**
     * @var string|null The string type (might be used in documentation or PHP code).
     */
    protected $type;

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     *
     * @return static
     */
    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
