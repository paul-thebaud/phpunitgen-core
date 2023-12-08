<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Laravel\Command;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\Factories\ClassFactory as ClassFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory as DocumentationFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory as ImportFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\MethodFactory as MethodFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\PropertyFactory as PropertyFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory as StatementFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory as ValueFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator as TestGeneratorContract;
use PhpUnitGen\Core\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Factories\PropertyFactory;
use PhpUnitGen\Core\Generators\Factories\StatementFactory;
use PhpUnitGen\Core\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Generators\Tests\Laravel\Command\CommandMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Laravel\Command\CommandTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\FeatureClassFactory;
use PhpUnitGen\Core\Models\TestClass;
use ReflectionException;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class CommandTestGeneratorTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\Laravel\Command\CommandTestGenerator
 */
class CommandTestGeneratorTest extends TestCase
{
    public function testImplementations(): void
    {
        $implementations = CommandTestGenerator::implementations();

        self::assertSame([
            TestGeneratorContract::class        => CommandTestGenerator::class,
            ClassFactoryContract::class         => FeatureClassFactory::class,
            DocumentationFactoryContract::class => DocumentationFactory::class,
            ImportFactoryContract::class        => ImportFactory::class,
            MethodFactoryContract::class        => CommandMethodFactory::class,
            PropertyFactoryContract::class      => PropertyFactory::class,
            StatementFactoryContract::class     => StatementFactory::class,
            ValueFactoryContract::class         => ValueFactory::class,
        ], $implementations);

        foreach ($implementations as $contract => $implementation) {
            self::assertArrayHasKey($contract, class_implements($implementation));
        }
    }

    /**
     * @param bool                  $expected
     * @param bool                  $automatic
     * @param ReflectionMethod|Mock $method
     *
     * @throws ReflectionException
     *
     * @dataProvider isTestableDataProvider
     */
    public function testIsTestable(bool $expected, bool $automatic, ReflectionMethod $method): void
    {
        $class = Mockery::mock(TestClass::class);
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $barProperty = Mockery::mock(ReflectionProperty::class);

        $testGenerator = new CommandTestGenerator();

        $method->shouldReceive([
            'getDeclaringClass' => $reflectionClass,
        ]);

        $reflectionClass->shouldReceive([
            'getImmediateProperties' => [$barProperty],
        ]);

        $barProperty->shouldReceive([
            'getName'  => 'bar',
            'isStatic' => false,
        ]);

        $testGenerator->setConfig($config = Mockery::mock(Config::class));
        $config->shouldReceive(['automaticGeneration' => $automatic]);
        self::assertSame($expected, $this->callProtectedMethod($testGenerator, 'isTestable', $class, $method));
    }

    public static function isTestableDataProvider(): array
    {
        $staticMethod = Mockery::mock(ReflectionMethod::class);
        $getterMethod = Mockery::mock(ReflectionMethod::class);
        $method = Mockery::mock(ReflectionMethod::class);
        $handleMethod = Mockery::mock(ReflectionMethod::class);

        $staticMethod->shouldReceive([
            'getShortName' => 'make',
            'isStatic'     => true,
        ]);
        $getterMethod->shouldReceive([
            'getShortName' => 'getBar',
            'isStatic'     => false,
        ]);
        $method->shouldReceive([
            'getShortName' => 'bar',
            'isStatic'     => false,
        ]);
        $handleMethod->shouldReceive([
            'getShortName' => 'handle',
            'isStatic'     => false,
        ]);

        return [
            [false, false, $staticMethod],
            [false, false, $getterMethod],
            [false, false, $method],
            [false, false, $handleMethod],
            [false, true, $staticMethod],
            [true, true, $getterMethod],
            [false, true, $method],
            [true, true, $handleMethod],
        ];
    }
}
