<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Providers;

use League\Container\Definition\DefinitionInterface;
use League\Container\ReflectionContainer;
use League\Container\ServiceProvider\AbstractServiceProvider;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\ImportFactory as ImportFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator as MockGeneratorContract;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator as TestGeneratorContract;
use PhpUnitGen\Core\Contracts\Generators\ValueFactory as ValueFactoryContract;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Contracts\Renderers\Renderer as RendererContract;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

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
     * The contracts that this service provider must have defined at the registration end.
     */
    protected const REQUIRED_CONTRACTS = [
        CodeParserContract::class,
        ImportFactoryContract::class,
        MockGeneratorContract::class,
        RendererContract::class,
        TestGeneratorContract::class,
        ValueFactoryContract::class,
    ];

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

        $this->provides = self::REQUIRED_CONTRACTS;
        array_unshift($this->provides, Config::class);
    }

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        $this->leagueContainer->delegate(new ReflectionContainer());

        $this->leagueContainer->add(Config::class, $this->config);

        $implementations = $this->config->implementations();

        foreach ($implementations as $contract => $concrete) {
            $this->addDefinition($contract, $concrete);
        }

        if (array_diff(self::REQUIRED_CONTRACTS, array_keys($implementations))) {
            throw new InvalidArgumentException('missing contract implementation in config');
        }
    }

    /**
     * Add a contract's implementation to container with its arguments using reflection.
     *
     * @param string $contract
     * @param string $concrete
     *
     * @throws InvalidArgumentException
     */
    protected function addDefinition(string $contract, string $concrete): void
    {
        if (! in_array($contract, $this->provides)) {
            throw new InvalidArgumentException("contract {$contract} implementation is not necessary");
        }

        if (! class_exists($concrete)) {
            throw new InvalidArgumentException("class {$concrete} does not exists");
        }

        if (! in_array($contract, class_implements($concrete))) {
            throw new InvalidArgumentException("class {$concrete} does not implements {$contract}");
        }

        $definition = $this->leagueContainer->add($contract, $concrete);

        $this->addDefinitionArguments($definition);
    }

    /**
     * Add the necessary arguments to the definition.
     *
     * @param DefinitionInterface $definition
     *
     * @throws InvalidArgumentException
     */
    protected function addDefinitionArguments(DefinitionInterface $definition): void
    {
        $constructor = $this->getClassConstructor($definition);

        if (! $constructor) {
            return;
        }

        foreach ($constructor->getParameters() as $parameter) {
            $this->addDefinitionArgument($definition, $parameter);
        }
    }

    /**
     * Add an argument to definition from the given parameter.
     *
     * @param DefinitionInterface $definition
     * @param ReflectionParameter $parameter
     *
     * @throws InvalidArgumentException
     */
    protected function addDefinitionArgument(DefinitionInterface $definition, ReflectionParameter $parameter): void
    {
        $type = $parameter->getType();
        if (! $type || $type->isBuiltin()) {
            throw new InvalidArgumentException(
                "dependency {$parameter->getName()} for class {$definition->getConcrete()} has an unresolvable type"
            );
        }

        $definition->addArgument((string) $type);
    }

    /**
     * Get the constructor for a definition concrete class.
     *
     * @param DefinitionInterface $definition
     *
     * @return ReflectionMethod|null
     *
     * @throws InvalidArgumentException
     */
    protected function getClassConstructor(DefinitionInterface $definition): ?ReflectionMethod
    {
        try {
            return (new ReflectionClass($definition->getConcrete()))->getMethod('__construct');
        } catch (ReflectionException $exception) {
            return null;
        }
    }
}
