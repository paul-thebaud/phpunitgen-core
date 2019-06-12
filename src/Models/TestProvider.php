<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\Concerns\HasTestMethodParent;

/**
 * Class TestParameter.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestProvider implements Renderable
{
    use HasTestMethodParent;

    /**
     * @var string The name of the provider method.
     */
    protected $name;

    /**
     * @var array The data this provider provides.
     */
    protected $data;

    /**
     * TestProvider constructor.
     *
     * @param TestMethod $testMethod
     * @param string     $name
     * @param array      $data
     */
    public function __construct(TestMethod $testMethod, string $name, array $data)
    {
        $this->testMethod = $testMethod->setProvider($this);

        $this->name = $name;
        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Renderer $renderer): void
    {
        $renderer->visitTestProvider($this);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }
}
