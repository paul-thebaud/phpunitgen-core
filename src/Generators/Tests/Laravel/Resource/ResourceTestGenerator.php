<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Resource;

use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory as MethodFactoryContract;
use PhpUnitGen\Core\Generators\Tests\Concerns\ChecksMethods;
use PhpUnitGen\Core\Generators\Tests\Laravel\LaravelTestGenerator;
use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Class ResourceTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class ResourceTestGenerator extends LaravelTestGenerator
{
    use ChecksMethods;

    /**
     * {@inheritdoc}
     */
    public static function implementations(): array
    {
        return array_merge(parent::implementations(), [
            MethodFactoryContract::class => ResourceMethodFactory::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function isTestable(TestClass $class, ReflectionMethod $reflectionMethod): bool
    {
        return $this->config->automaticGeneration()
            && ($this->isGetterOrSetter($reflectionMethod) || $this->isMethod($reflectionMethod, 'toArray'));
    }
}
