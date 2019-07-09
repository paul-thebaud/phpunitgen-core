<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Providers;

use League\Container\Container;
use League\Container\ServiceProvider\AbstractServiceProvider;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\ImportFactory as ImportFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator as MockGeneratorContract;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator as TestGeneratorContract;
use PhpUnitGen\Core\Contracts\Generators\ValueFactory as ValueFactoryContract;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Contracts\Renderers\Renderer as RendererContract;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator;
use PhpUnitGen\Core\Generators\Mocks\PhpUnitMockGenerator;
use PhpUnitGen\Core\Generators\Tests\BasicTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\PolicyTestGenerator;
use PhpUnitGen\Core\Parsers\CodeParser;
use PhpUnitGen\Core\Renderers\Renderer;
use Roave\BetterReflection\BetterReflection;

/**
 * Class CoreServiceProvider.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class CoreServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string[]
     */
    protected $provides = [
        Config::class,
        CodeParserContract::class,
        ImportFactoryContract::class,
        MockGeneratorContract::class,
        RendererContract::class,
        TestGeneratorContract::class,
        ValueFactoryContract::class,
    ];

    /**
     * @var callable[] The mock generator resolvers.
     */
    protected $mockGeneratorResolvers = [];

    /**
     * @var callable[] The test generator resolvers.
     */
    protected $testGeneratorResolvers = [];

    /**
     * @var Config
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
     * {@inheritdoc}
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
        $this->getLeagueContainer()
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
        $container = $this->getLeagueContainer();

        $container->add(CodeParserContract::class, CodeParser::class)
            ->addArgument(BetterReflection::class);

        $container->add(ImportFactoryContract::class, ImportFactory::class);

        $container->add(RendererContract::class, Renderer::class);

        $container->add(ValueFactoryContract::class, ValueFactory::class)
            ->addArgument(MockGeneratorContract::class);

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
                $container->add(MockGeneratorContract::class, PhpUnitMockGenerator::class)
                    ->addArgument(ImportFactory::class);
            })
            ->addMockGeneratorResolver('mockery', function (Container $container) {
                $container->add(MockGeneratorContract::class, MockeryMockGenerator::class)
                    ->addArgument(ImportFactory::class);
            });
    }

    /**
     * Resolve the mock generator mapped with the given key in container.
     *
     * @return static
     */
    protected function callMockGeneratorResolver(): self
    {
        $selected = $this->config->mockWith();

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
                $container->add(TestGeneratorContract::class, BasicTestGenerator::class)
                    ->addArgument(Config::class)
                    ->addArgument(MockGeneratorContract::class)
                    ->addArgument(ImportFactoryContract::class)
                    ->addArgument(ValueFactoryContract::class);
            })
            ->addTestGeneratorResolver('laravel.policy', function (Container $container) {
                $container->add(TestGeneratorContract::class, PolicyTestGenerator::class)
                    ->addArgument(Config::class)
                    ->addArgument(MockGeneratorContract::class)
                    ->addArgument(ImportFactoryContract::class)
                    ->addArgument(ValueFactoryContract::class);
            });
    }

    /**
     * Resolve the test generator mapped with the given key in container.
     *
     * @return static
     */
    protected function callTestGeneratorResolver(): self
    {
        $selected = $this->config->generateWith();

        if (! array_key_exists($selected, $this->testGeneratorResolvers)) {
            throw new InvalidArgumentException("{$selected} test generator cannot be resolved");
        }

        $this->testGeneratorResolvers[$selected]($this->getLeagueContainer());

        return $this;
    }
}
