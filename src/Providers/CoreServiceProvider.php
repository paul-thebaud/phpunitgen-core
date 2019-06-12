<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Providers;

use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator as MockGeneratorContract;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator as TestGeneratorContract;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Contracts\Renderers\Renderer as RendererContract;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\BasicTestGenerator;
use PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator;
use PhpUnitGen\Core\Generators\Mocks\PhpUnitMockGenerator;
use PhpUnitGen\Core\Parsers\CodeParser;
use PhpUnitGen\Core\Renderers\Renderer;
use Roave\BetterReflection\BetterReflection;

/**
 * Class CoreServiceProvider.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class CoreServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string[] $provides
     */
    protected $provides = [
        Config::class,
        CodeParserContract::class,
        MockGeneratorContract::class,
        TestGeneratorContract::class,
        RendererContract::class,
    ];

    /**
     * @var callable[] $mockGeneratorResolvers The mock generator resolvers.
     */
    protected $mockGeneratorResolvers = [];

    /**
     * @var callable[] $testGeneratorResolvers The test generator resolvers.
     */
    protected $testGeneratorResolvers = [];

    /**
     * @var Config $config
     */
    protected $config;

    /**
     * CoreServiceProvider constructor.
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;

        $this
            ->addDefaultMockGeneratorResolvers()
            ->addDefaultTestGeneratorResolvers();
    }

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        $this
            ->addConcretes()
            ->addImplementations()
            ->callMockGeneratorResolver()
            ->callTestGeneratorResolver();
    }

    /**
     * Add contracts implementations to container.
     *
     * @return static
     */
    protected function addConcretes(): self
    {
        $this->getContainer()
            ->add(Config::class, $this->config);

        return $this;
    }

    /**
     * Add contracts implementations to container.
     *
     * @return static
     */
    protected function addImplementations(): self
    {
        $this->getContainer()
            ->add(CodeParserContract::class, CodeParser::class)
            ->addArgument(BetterReflection::class);

        $this->getContainer()
            ->add(RendererContract::class, Renderer::class)
            ->addArgument(Config::class);

        return $this;
    }

    /**
     * Add the given resolver for mock generator and map it with the given key.
     *
     * @param string   $key
     * @param callable $resolver
     *
     * @return static
     */
    public function addMockGeneratorResolver(string $key, callable $resolver): self
    {
        $this->mockGeneratorResolvers[$key] = $resolver;

        return $this;
    }

    /**
     * Add the default PhpUnitGen mock generator resolvers.
     *
     * @return static
     */
    protected function addDefaultMockGeneratorResolvers(): self
    {
        return $this
            ->addMockGeneratorResolver('phpunit', function (Container $container) {
                $container->add(MockGeneratorContract::class, PhpUnitMockGenerator::class);
            })
            ->addMockGeneratorResolver('mockery', function (Container $container) {
                $container->add(MockGeneratorContract::class, MockeryMockGenerator::class);
            });
    }

    /**
     * Resolve the mock generator mapped with the given key in container.
     *
     * @return static
     */
    protected function callMockGeneratorResolver(): self
    {
        $selected = $this->config->getMockWith();

        if (! array_key_exists($selected, $this->mockGeneratorResolvers)) {
            throw new InvalidArgumentException("{$selected} mock generator cannot be resolved");
        }

        $this->mockGeneratorResolvers[$selected]($this->getContainer());

        return $this;
    }

    /**
     * Add the given resolver for test generator and map it with the given key.
     *
     * @param string   $key
     * @param callable $resolver
     *
     * @return static
     */
    public function addTestGeneratorResolver(string $key, callable $resolver): self
    {
        $this->testGeneratorResolvers[$key] = $resolver;

        return $this;
    }

    /**
     * Add the default PhpUnitGen mock generator resolvers.
     *
     * @return static
     */
    protected function addDefaultTestGeneratorResolvers(): self
    {
        return $this
            ->addTestGeneratorResolver('basic', function (Container $container) {
                $container->add(TestGeneratorContract::class, BasicTestGenerator::class);
            });
    }

    /**
     * Resolve the test generator mapped with the given key in container.
     *
     * @return static
     */
    protected function callTestGeneratorResolver(): self
    {
        $selected = $this->config->getGenerateWith();

        if (! array_key_exists($selected, $this->testGeneratorResolvers)) {
            throw new InvalidArgumentException("{$selected} test generator cannot be resolved");
        }

        $this->testGeneratorResolvers[$selected]($this->getContainer());

        return $this;
    }
}
