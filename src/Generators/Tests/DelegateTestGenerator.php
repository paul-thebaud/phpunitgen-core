<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests;

use PhpUnitGen\Core\Aware\ConfigAwareTrait;
use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Container\CoreContainerFactory;
use PhpUnitGen\Core\Contracts\Aware\ConfigAware;
use PhpUnitGen\Core\Contracts\Config\Config as ConfigContract;
use PhpUnitGen\Core\Contracts\Generators\DelegateTestGenerator as DelegateTestGeneratorContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ClassFactory;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator;
use PhpUnitGen\Core\Exceptions\RuntimeException;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Channel\ChannelTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Command\CommandTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Controller\ControllerTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Job\JobTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\LaravelTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Listener\ListenerTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Resource\ResourceTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\Rule\RuleTestGenerator;
use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\TestClass;
use Psr\Container\ContainerInterface;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Class DelegateTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class DelegateTestGenerator implements DelegateTestGeneratorContract, ConfigAware
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
        return $this->getDelegate($reflectionClass)->generate($reflectionClass);
    }

    /**
     * {@inheritdoc}
     */
    public function canGenerateFor(ReflectionClass $reflectionClass): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getDelegate(ReflectionClass $reflectionClass): TestGenerator
    {
        $testGeneratorClass = $this->chooseTestGenerator($reflectionClass);
        $config = $this->makeNewConfiguration($testGeneratorClass);

        return $this->makeNewContainer($config)->get(TestGenerator::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassFactory(): ClassFactory
    {
        throw new RuntimeException(
            'getClassFactory method should not be called on a DelegateTestGenerator'
        );
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
        if ($this->isLaravelContext()) {
            return $this->chooseTestGeneratorForLaravel($reflectionClass);
        }

        return BasicTestGenerator::class;
    }

    /**
     * Check if the context of class is a Laravel project.
     *
     * @return bool
     */
    protected function isLaravelContext(): bool
    {
        return $this->config->getOption('context') === 'laravel';
    }

    /**
     * Choose TestGenerator which should be used for the given reflection class in a Laravel context.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return string
     */
    protected function chooseTestGeneratorForLaravel(ReflectionClass $reflectionClass): string
    {
        $reflectionClassName = $reflectionClass->getName();
        foreach ($this->getNamespaceGeneratorMappingForLaravel() as $namespace => $generator) {
            if (Str::contains($namespace, $reflectionClassName)) {
                return $generator;
            }
        }

        return LaravelTestGenerator::class;
    }

    /**
     * Get the mapping between the namespace the class should be in and the associated generator.
     *
     * @return array
     */
    protected function getNamespaceGeneratorMappingForLaravel(): array
    {
        return [
            '\\Broadcasting\\'      => ChannelTestGenerator::class,
            '\\Console\\Commands\\' => CommandTestGenerator::class,
            '\\Http\\Controllers\\' => ControllerTestGenerator::class,
            '\\Jobs\\'              => JobTestGenerator::class,
            '\\Listeners\\'         => ListenerTestGenerator::class,
            '\\Policies\\'          => PolicyTestGenerator::class,
            '\\Http\\Resources\\'   => ResourceTestGenerator::class,
            '\\Rules\\'             => RuleTestGenerator::class,
        ];
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
        $implementationsKey = 'implementations';

        $configArray = $this->config->toArray();
        $oldImplementations = $configArray[$implementationsKey];

        $configArray[$implementationsKey] = call_user_func([
            $testGeneratorClass,
            $implementationsKey,
        ]);
        unset($oldImplementations[TestGenerator::class]);
        $configArray[$implementationsKey] = array_merge(
            $configArray[$implementationsKey],
            $oldImplementations
        );

        return Config::make($configArray);
    }

    /**
     * Make the new container from the new configuration.
     *
     * @param ConfigContract $config
     *
     * @return ContainerInterface
     */
    protected function makeNewContainer(ConfigContract $config): ContainerInterface
    {
        return CoreContainerFactory::make($config);
    }
}
