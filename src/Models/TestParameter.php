<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\Concerns\HasTestMethodParent;

/**
 * Class TestParameter.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestParameter implements Renderable
{
    use HasTestMethodParent;

    /**
     * @var string $name The name of the parameter.
     */
    protected $name;

    /**
     * @var string|null $type The type of the parameter.
     */
    protected $type;

    /**
     * TestParameter constructor.
     *
     * @param TestMethod  $testMethod
     * @param string      $name
     * @param string|null $type
     */
    public function __construct(TestMethod $testMethod, string $name, ?string $type = null)
    {
        $this->testMethod = $testMethod->addParameter($this);

        $this->name = $name;
        $this->type = $type;
    }

    /**
     * {@inheritDoc}
     */
    public function accept(Renderer $renderer): void
    {
        $renderer->visitTestParameter($this);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }
}
