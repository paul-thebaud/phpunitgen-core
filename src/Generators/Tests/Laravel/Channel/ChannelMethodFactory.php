<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Channel;

use PhpUnitGen\Core\Contracts\Aware\ConfigAware;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Concerns\ChecksMethods;
use PhpUnitGen\Core\Generators\Tests\Laravel\Concerns\UsesUserModel;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use Roave\BetterReflection\Reflection\ReflectionMethod;

/**
 * Class ChannelMethodFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class ChannelMethodFactory extends BasicMethodFactory implements ConfigAware
{
    use ChecksMethods;
    use UsesUserModel;

    /**
     * {@inheritdoc}
     */
    public function makeSetUp(TestClass $class): TestMethod
    {
        $method = parent::makeSetUp($class);

        $this->makeUserAffectStatement($class, $method);

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

        if (! $this->isMethod($reflectionMethod, 'join')) {
            throw new InvalidArgumentException(
                "cannot generate tests for method {$reflectionMethod->getShortName()}, not a \"join\" method"
            );
        }

        $this->addJoinTestMethod($class, $reflectionMethod, 'Unauthorized', 'false');
        $this->addJoinTestMethod($class, $reflectionMethod, 'Authorized', 'true');
    }

    /**
     * Make and add a method to test a join case (authorized or unauthorized).
     *
     * @param TestClass        $class
     * @param ReflectionMethod $reflectionMethod
     * @param string           $suffix
     * @param string           $assert
     */
    protected function addJoinTestMethod(
        TestClass $class,
        ReflectionMethod $reflectionMethod,
        string $suffix,
        string $assert
    ): void {
        $instanceName = $this->getPropertyName($class->getReflectionClass());

        $method = $this->makeEmpty($reflectionMethod, 'When'.$suffix);
        $method->addStatement($this->statementFactory->makeTodo('This test is incomplete.'));
        $method->addStatement(
            $this->statementFactory->makeAssert($assert, "\$this->{$instanceName}->join(\$this->user)")
        );

        $class->addMethod($method);
    }
}
