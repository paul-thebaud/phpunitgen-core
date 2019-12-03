<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Container;

use League\Container\Definition\DefinitionInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Class ReflectionServiceProvider.
 *
 * This service provider contains the "addDefinition" method to add an alias
 * and its required constructor arguments using reflection.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
abstract class ReflectionServiceProvider extends AbstractServiceProvider
{
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
    private function addDefinitionArguments(DefinitionInterface $definition): void
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
    private function addDefinitionArgument(DefinitionInterface $definition, ReflectionParameter $parameter): void
    {
        $type = $parameter->getType();
        if (! $type || $type->isBuiltin()) {
            throw new InvalidArgumentException(
                "dependency {$parameter->getName()} for class {$definition->getConcrete()} has an unresolvable type"
            );
        }

        $definition->addArgument($type->getName());
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
    private function getClassConstructor(DefinitionInterface $definition): ?ReflectionMethod
    {
        try {
            return (new ReflectionClass($definition->getConcrete()))->getMethod('__construct');
        } catch (ReflectionException $exception) {
            return null;
        }
    }
}
