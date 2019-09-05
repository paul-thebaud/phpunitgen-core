<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Policy;

use PhpUnitGen\Core\Aware\DocumentationFactoryAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\DocumentationFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\ClassFactory as ClassFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory as MethodFactoryContract;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\UnitClassFactory;
use PhpUnitGen\Core\Generators\Tests\Laravel\UsesUserModel;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestProperty;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Class PolicyTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class PolicyTestGenerator extends BasicTestGenerator implements DocumentationFactoryAware
{
    use DocumentationFactoryAwareTrait;
    use UsesUserModel;

    /**
     * {@inheritdoc}
     */
    public static function implementations(): array
    {
        return array_merge(parent::implementations(), [
            ClassFactoryContract::class  => UnitClassFactory::class,
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

        $userImport = $this->getUserClass($class);

        $userProperty = new TestProperty('user');
        $userProperty->setDocumentation(
            $this->documentationFactory->makeForProperty($userProperty, $userImport)
        );
        $class->addProperty($userProperty);
    }
}
