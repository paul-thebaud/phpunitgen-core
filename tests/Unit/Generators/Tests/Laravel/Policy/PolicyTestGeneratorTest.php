<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Laravel\Policy;

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
use PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\UnitClassFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestProperty;
use ReflectionException;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class PolicyTestGeneratorTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyTestGenerator
 */
class PolicyTestGeneratorTest extends TestCase
{
    public function testImplementations(): void
    {
        $implementations = PolicyTestGenerator::implementations();

        self::assertSame([
            TestGeneratorContract::class        => PolicyTestGenerator::class,
            ClassFactoryContract::class         => UnitClassFactory::class,
            DocumentationFactoryContract::class => DocumentationFactory::class,
            ImportFactoryContract::class        => ImportFactory::class,
            MethodFactoryContract::class        => PolicyMethodFactory::class,
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

        $testGenerator = new PolicyTestGenerator();

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

        return [
            [false, false, $staticMethod],
            [false, false, $getterMethod],
            [false, false, $method],
            [false, true, $staticMethod],
            [true, true, $getterMethod],
            [true, true, $method],
        ];
    }

    public function testAddProperties(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $class = Mockery::mock(TestClass::class);
        $classProperty = Mockery::mock(TestProperty::class);
        $userProperty = Mockery::mock(TestProperty::class);
        $propertyFactory = Mockery::mock(PropertyFactoryContract::class);
        $config = Mockery::mock(Config::class);
        $importFactory = Mockery::mock(ImportFactoryContract::class);

        $testGenerator = new PolicyTestGenerator();
        $testGenerator->setPropertyFactory($propertyFactory);
        $testGenerator->setConfig($config);
        $testGenerator->setImportFactory($importFactory);

        $propertyFactory->shouldReceive('makeForClass')
            ->with($class)
            ->andReturn($classProperty);

        $reflectionClass->shouldReceive(['getImmediateMethods' => []]);

        $config->shouldReceive('getOption')
            ->with('laravel.user', 'App\\User')
            ->andReturn('App\\Models\\User');

        $propertyFactory->shouldReceive('makeCustom')
            ->with($class, 'user', 'App\\Models\\User', false, false)
            ->andReturn($userProperty);

        $class->shouldReceive(['getReflectionClass' => $reflectionClass]);
        $class->shouldReceive('addProperty')
            ->with($classProperty);
        $class->shouldReceive('addProperty')
            ->with($userProperty);

        self::assertNull($this->callProtectedMethod($testGenerator, 'addProperties', $class));
    }
}
