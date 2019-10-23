<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Channel;

use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory as MethodFactoryContract;
use PhpUnitGen\Core\Generators\Tests\Concerns\ChecksMethods;
use PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyTestGenerator;
use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Class ChannelTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class ChannelTestGenerator extends PolicyTestGenerator
{
    use ChecksMethods;

    /**
     * {@inheritdoc}
     */
    public static function implementations(): array
    {
        return array_merge(parent::implementations(), [
            MethodFactoryContract::class => ChannelMethodFactory::class,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function isTestable(TestClass $class, ReflectionMethod $reflectionMethod): bool
    {
        return $this->config->automaticGeneration()
            && ($this->isGetterOrSetter($reflectionMethod) || $this->isMethod($reflectionMethod, 'join'));
    }
}
