<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Models;

use PhpUnitGen\Core\Contracts\Renderers\Renderable;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\Concerns\HasTestClassParent;

/**
 * Class TestProperty.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class TestProperty implements Renderable
{
    use HasTestClassParent;

    /**
     * @var string The name of the property.
     */
    protected $name;

    /**
     * @var string The name of the class (not including namespace).
     */
    protected $class;

    /**
     * @var bool If this property corresponds to the tested class instance.
     */
    protected $isTestedClass;

    /**
     * TestProperty constructor.
     *
     * @param TestClass $testClass
     * @param string    $name
     * @param string    $class
     * @param bool      $isTestedClass
     */
    public function __construct(
        TestClass $testClass,
        string $name,
        string $class,
        bool $isTestedClass = false
    ) {
        $this->testClass = $testClass->addProperty($this);

        $this->name = $name;
        $this->class = $class;
        $this->isTestedClass = $isTestedClass;
    }

    /**
     * {@inheritdoc}
     */
    public function accept(Renderer $renderer): void
    {
        $renderer->visitTestProperty($this);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @return bool
     */
    public function isTestedClass(): bool
    {
        return $this->isTestedClass;
    }
}
