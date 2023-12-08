<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Factories;

use PhpUnitGen\Core\Aware\ImportFactoryAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\ImportFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory as StatementFactoryContract;
use PhpUnitGen\Core\Generators\Concerns\InstantiatesClass;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Tightenco\Collect\Support\Collection;

/**
 * Class StatementFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class StatementFactory implements StatementFactoryContract, ImportFactoryAware
{
    use ImportFactoryAwareTrait;
    use InstantiatesClass;

    /**
     * {@inheritdoc}
     */
    public function makeTodo(string $todo): TestStatement
    {
        return new TestStatement("/** @todo {$todo} */");
    }

    /**
     * {@inheritdoc}
     */
    public function makeAffect(string $name, string $value, bool $isProperty = true): TestStatement
    {
        $statement = new TestStatement('$');

        if ($isProperty) {
            $statement->append('this->');
        }

        return $statement->append($name)
            ->append(' = ')
            ->append($value);
    }

    /**
     * {@inheritdoc}
     */
    public function makeAssert(string $assert, string ...$parameters): TestStatement
    {
        return (new TestStatement('self::assert'))
            ->append(ucfirst($assert))
            ->append('(')
            ->append(implode(', ', $parameters))
            ->append(')');
    }

    /**
     * {@inheritdoc}
     */
    public function makeInstantiation(TestClass $class, Collection $parameters): TestStatement
    {
        $reflectionClass = $class->getReflectionClass();

        $className = $this->importFactory->make($class, $reflectionClass->getName())
            ->getFinalName();

        $parametersString = $parameters
            ->map(function (ReflectionParameter $reflectionParameter) {
                return '$this->'.$reflectionParameter->getName();
            })
            ->implode(', ');

        $statement = $this->makeAffect($this->getPropertyName($reflectionClass), '');

        if (! $reflectionClass->isAbstract() && ! $reflectionClass->isTrait()) {
            return $statement->append('new ')
                ->append($className)
                ->append('(')
                ->append($parametersString)
                ->append(')');
        }

        $statement->append('$this->getMockBuilder(')
            ->append($className)
            ->append('::class)')
            ->addLine('->setConstructorArgs([')
            ->append($parametersString)
            ->append('])');

        if ($reflectionClass->isAbstract()) {
            return $statement->addLine('->getMockForAbstractClass()');
        }

        return $statement->addLine('->getMockForTrait()');
    }
}
