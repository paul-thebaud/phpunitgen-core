<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\Concerns\HasTestMethodParent;

/**
 * Class TestStatement.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestStatement implements Renderable
{
    use HasTestMethodParent;

    /**
     * @var string The statement.
     */
    protected $statement;

    /**
     * TestStatement constructor.
     *
     * @param string $statement
     */
    public function __construct(string $statement)
    {
        $this->statement = $statement;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Renderer $renderer): void
    {
        $renderer->visitTestStatement($this);
    }

    /**
     * @return string
     */
    public function getStatement(): string
    {
        return $this->statement;
    }
}
