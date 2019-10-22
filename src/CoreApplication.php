<?php

declare(strict_types=1);

namespace PhpUnitGen\Core;

use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Container\CoreContainerFactory;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser;
use PhpUnitGen\Core\Contracts\Parsers\Source;
use PhpUnitGen\Core\Contracts\Renderers\Rendered;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use Psr\Container\ContainerInterface;

/**
 * Class CoreApplication.
 *
 * The application which execute all PhpUnitGen steps to generate tests.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class CoreApplication
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * CoreApplication constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Create an application using the given config array.
     *
     * @param array $config
     *
     * @return CoreApplication
     */
    public static function make(array $config = []): self
    {
        return new static(CoreContainerFactory::make(Config::make($config)));
    }

    /**
     * Run PhpUnitGen on the given source and return the renderer result.
     *
     * @param Source $source
     *
     * @return Rendered
     */
    public function run(Source $source): Rendered
    {
        $reflectionClass = $this->getCodeParser()->parse($source);
        $testClass = $this->getTestGenerator()->generate($reflectionClass);

        return $this->getRenderer()->visitTestClass($testClass)->getRendered();
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return CodeParser
     */
    public function getCodeParser(): CodeParser
    {
        return $this->getContainer()->get(CodeParser::class);
    }

    /**
     * @return TestGenerator
     */
    public function getTestGenerator(): TestGenerator
    {
        return $this->getContainer()->get(TestGenerator::class);
    }

    /**
     * @return Renderer
     */
    public function getRenderer(): Renderer
    {
        return $this->getContainer()->get(Renderer::class);
    }
}
