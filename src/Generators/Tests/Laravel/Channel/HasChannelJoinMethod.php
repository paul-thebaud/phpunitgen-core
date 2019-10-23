<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Channel;

use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Trait HasChannelJoinMethod.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait HasChannelJoinMethod
{
    /**
     * Check if the given method is a Laravel channel "join" method.
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return bool
     */
    protected function isChannelJoinMethod(ReflectionMethod $reflectionMethod): bool
    {
        return ! $reflectionMethod->isStatic()
            && $reflectionMethod->getShortName() === 'join';
    }
}
