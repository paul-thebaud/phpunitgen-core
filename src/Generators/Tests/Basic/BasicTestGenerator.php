<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Basic;

use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory as MethodFactoryContract;
use PhpUnitGen\Core\Generators\Tests\AbstractTestGenerator;
use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Class BasicTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class BasicTestGenerator extends AbstractTestGenerator
{
    use ManagesGetterAndSetter;

    /**
     * {@inheritdoc}
     */
    public static function implementations(): array
    {
        return array_merge(parent::implementations(), [
            MethodFactoryContract::class => BasicMethodFactory::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function isTestable(TestClass $class, ReflectionMethod $reflectionMethod): bool
    {
        return $this->config->automaticGeneration() && $this->isGetterOrSetter($reflectionMethod);
    }

    /**
     * {@inheritdoc}
     */
    protected function handleForTestable(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        $this->methodFactory->makeTestable($class, $reflectionMethod);
    }
}
