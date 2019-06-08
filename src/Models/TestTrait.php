<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\Concerns\HasTestClassParent;

/**
 * Class TestTrait.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestTrait implements Renderable
{
    use HasTestClassParent;

    /**
     * @var string $name The name of the class (not including namespace).
     */
    protected $name;

    /**
     * TestTrait constructor.
     *
     * @param TestClass $testClass
     * @param string    $name
     */
    public function __construct(TestClass $testClass, string $name)
    {
        $this->testClass = $testClass->addTrait($this);

        $this->name = $name;
    }

    /**
     * {@inheritDoc}
     */
    public function accept(Renderer $renderer): void
    {
        $renderer->visitTestTrait($this);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
}
