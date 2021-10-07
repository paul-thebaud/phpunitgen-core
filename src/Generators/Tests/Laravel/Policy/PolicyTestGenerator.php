<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Policy;

use PhpUnitGen\Core\Contracts\Aware\StatementFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory as MethodFactoryContract;
use PhpUnitGen\Core\Generators\Tests\Laravel\Concerns\UsesUserModel;
use PhpUnitGen\Core\Generators\Tests\Laravel\LaravelTestGenerator;
use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Class PolicyTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class PolicyTestGenerator extends LaravelTestGenerator implements StatementFactoryAware
{
    use UsesUserModel;

    /**
     * {@inheritdoc}
     */
    public static function implementations(): array
    {
        return array_merge(parent::implementations(), [
            MethodFactoryContract::class => PolicyMethodFactory::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function isTestable(TestClass $class, ReflectionMethod $reflectionMethod): bool
    {
        return $this->config->automaticGeneration()
            && ($this->isGetterOrSetter($reflectionMethod) || ! $reflectionMethod->isStatic());
    }

    /**
     * {@inheritdoc}
     */
    protected function addProperties(TestClass $class): void
    {
        parent::addProperties($class);

        $class->addProperty(
            $this->propertyFactory->makeCustom($class, 'user', $this->getUserClassAsString(), false, false)
        );
    }
}
