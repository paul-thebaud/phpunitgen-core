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
class TestProvider implements Renderable
{
    use HasTestMethodParent;

    /**
     * @var array $data The data this provider provides.
     */
    protected $data;

    /**
     * TestProvider constructor.
     *
     * @param TestMethod $testMethod
     * @param array      $data
     */
    public function __construct(TestMethod $testMethod, array $data)
    {
        $this->testMethod = $testMethod->setProvider($this);

        $this->data = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function accept(Renderer $renderer): void
    {
        $renderer->visitTestProvider($this);
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
