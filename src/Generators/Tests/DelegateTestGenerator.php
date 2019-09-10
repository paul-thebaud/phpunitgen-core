<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests;

use League\Container\Container;
use PhpUnitGen\Core\Aware\ConfigAwareTrait;
use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Contracts\Aware\ConfigAware;
use PhpUnitGen\Core\Contracts\Config\Config as ConfigContract;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyTestGenerator;
use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Providers\CoreServiceProvider;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Class DelegateTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class DelegateTestGenerator implements TestGenerator, ConfigAware
{
    use ConfigAwareTrait;

    /**
     * {@inheritdoc}
     */
    public static function implementations(): array
    {
        return [
            TestGenerator::class => static::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generate(ReflectionClass $reflectionClass): TestClass
    {
        if (! $this->canGenerateFor($reflectionClass)) {
            throw new InvalidArgumentException(
                'cannot generate tests for given reflection class'
            );
        }

        $testGeneratorClass = $this->chooseTestGenerator($reflectionClass);
        $config = $this->makeNewConfiguration($testGeneratorClass);
        $testGenerator = $this->makeNewTestGenerator($config);

        return $testGenerator->generate($reflectionClass);
    }

    /**
     * {@inheritdoc}
     */
    public function canGenerateFor(ReflectionClass $reflectionClass): bool
    {
        return ! ($reflectionClass->isInterface() || $reflectionClass->isAnonymous());
    }

    /**
     * Choose TestGenerator which should be used for the given reflection class.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return string
     */
    protected function chooseTestGenerator(ReflectionClass $reflectionClass): string
    {
        if (Str::contains('\\Policies\\', $reflectionClass->getName())) {
            return PolicyTestGenerator::class;
        }

        return BasicTestGenerator::class;
    }

    /**
     * Make the new config with chosen generator.
     *
     * @param string $testGeneratorClass
     *
     * @return ConfigContract
     */
    protected function makeNewConfiguration(string $testGeneratorClass): ConfigContract
    {
        $configArray = $this->config->toArray();
        $configArray['implementations'] = call_user_func([
            $testGeneratorClass,
            'implementations',
        ]);

        $oldImplementations = $this->config->implementations();
        unset($oldImplementations[TestGenerator::class]);

        $configArray['implementations'] = array_merge(
            $configArray['implementations'],
            $oldImplementations
        );

        return Config::make($configArray);
    }

    /**
     * Make the new test generator from the new configuration.
     *
     * @param ConfigContract $config
     *
     * @return TestGenerator
     */
    protected function makeNewTestGenerator(ConfigContract $config): TestGenerator
    {
        $container = new Container();
        $container->addServiceProvider(
            new CoreServiceProvider($config)
        );

        return $container->get(TestGenerator::class);
    }
}
