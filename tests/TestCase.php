<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use ReflectionClass;
use ReflectionException;

/**
 * Class TestCase.
 */
class TestCase extends PHPUnitTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * Call the given method on the given instance/class using reflection.
     *
     * @param object|string $target
     * @param string        $method
     * @param mixed         ...$args
     *
     * @return mixed
     *
     * @throws ReflectionException
     */
    protected function callProtectedMethod($target, string $method, ...$args)
    {
        $reflectionMethod = (new ReflectionClass($target))->getMethod($method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod->invoke(is_object($target) ? $target : null, ...$args);
    }
}
