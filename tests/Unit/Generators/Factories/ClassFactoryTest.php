<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Factories;

use Mockery;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Generators\Factories\ClassFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class ClassFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Factories\ClassFactory
 */
class ClassFactoryTest extends TestCase
{
    /**
     * @param string $baseNamespace
     * @param string $testNamespace
     * @param string $className
     * @param string $testName
     *
     * @dataProvider makeDataProvider
     */
    public function testMake(string $baseNamespace, string $testNamespace, string $className, string $testName): void
    {
        $config = Mockery::mock(Config::class);
        $documentationFactory = Mockery::mock(DocumentationFactory::class);
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $config->shouldReceive('baseTestNamespace')
            ->withNoArgs()
            ->andReturn($testNamespace);
        $config->shouldReceive('baseNamespace')
            ->withNoArgs()
            ->andReturn($baseNamespace);

        $reflectionClass->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn($className);

        $documentationFactory->shouldReceive('makeForClass')
            ->with(Mockery::type(TestClass::class))
            ->andReturn(Mockery::mock(TestDocumentation::class));

        $classFactory = new ClassFactory();
        $classFactory->setConfig($config);
        $classFactory->setDocumentationFactory($documentationFactory);

        $class = $classFactory->make($reflectionClass);

        self::assertSame($reflectionClass, $class->getReflectionClass());
        self::assertSame($testName, $class->getName());
        self::assertInstanceOf(TestDocumentation::class, $class->getDocumentation());
    }

    public function makeDataProvider(): array
    {
        return [
            ['', 'Tests\\Unit', 'Foo\\Bar', 'Tests\\Unit\\Foo\\BarTest'],
            ['App', 'Tests\\Unit', 'App\\Foo\\Bar', 'Tests\\Unit\\Foo\\BarTest'],
            ['\\App\\', '\\Tests\\Unit\\', 'App\\Foo\\Bar', 'Tests\\Unit\\Foo\\BarTest'],
        ];
    }
}
