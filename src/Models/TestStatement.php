<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\Concerns\HasTestMethodParent;

/**
 * Class TestStatement.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestStatement implements Renderable
{
    use HasTestMethodParent;

    /**
     * @var string $statement The statement.
     */
    protected $statement;

    /**
     * TestStatement constructor.
     *
     * @param TestMethod $testMethod
     * @param string     $statement
     */
    public function __construct(TestMethod $testMethod, string $statement)
    {
        $this->testMethod = $testMethod->addStatement($this);

        $this->statement = $statement;
    }

    /**
     * {@inheritDoc}
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
