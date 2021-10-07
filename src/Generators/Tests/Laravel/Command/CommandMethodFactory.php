<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Command;

use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Concerns\ChecksMethods;
use PhpUnitGen\Core\Generators\Tests\Laravel\Concerns\HasInstanceBinding;
use PhpUnitGen\Core\Helpers\Reflect;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Throwable;

/**
 * Class CommandMethodFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class CommandMethodFactory extends BasicMethodFactory
{
    use ChecksMethods;
    use HasInstanceBinding;

    /**
     * {@inheritdoc}
     */
    public function makeSetUp(TestClass $class): TestMethod
    {
        $method = parent::makeSetUp($class);

        $method->addStatement(
            $this->makeInstanceBindingStatement($class->getReflectionClass())
        );

        return $method;
    }

    /**
     * {@inheritdoc}
     */
    public function makeTestable(TestClass $class, ReflectionMethod $reflectionMethod): void
    {
        if ($this->isGetterOrSetter($reflectionMethod)) {
            parent::makeTestable($class, $reflectionMethod);

            return;
        }

        if (! $this->isMethod($reflectionMethod, 'handle')) {
            throw new InvalidArgumentException(
                "cannot generate tests for method {$reflectionMethod->getShortName()}, not a \"handle\" method"
            );
        }

        $method = $this->makeEmpty($reflectionMethod);

        $method->addStatement($this->statementFactory->makeTodo('This test is incomplete.'));
        $method->addStatement(
            (new TestStatement('$this->artisan(\''))
                ->append($this->resolveCommandSignature($class->getReflectionClass()))
                ->append('\')')
                ->addLine('->expectsOutput(\'Some expected output\')')
                ->addLine('->assertExitCode(0)')
        );

        $class->addMethod($method);
    }

    protected function resolveCommandSignature(ReflectionClass $reflectionClass): string
    {
        $signatureProperty = Reflect::property($reflectionClass, 'signature');

        if (! $signatureProperty
            || $signatureProperty->isStatic()
            || ! $signatureProperty->isProtected()
        ) {
            return 'command:name';
        }

        try {
            $signature = $signatureProperty->getDefaultValue();
        } catch (Throwable $exception) {
            $signature = null;
        }

        return is_string($signature) ? $signature : 'command:name';
    }
}
