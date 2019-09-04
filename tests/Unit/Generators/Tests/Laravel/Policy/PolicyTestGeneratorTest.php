<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Laravel\Policy;

use Mockery;
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
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestProperty;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
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

        $this->assertSame([
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
            $this->assertArrayHasKey($contract, class_implements($implementation));
        }
    }

    public function testIsTestable(): void
    {
        $class = Mockery::mock(TestClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $testGenerator = new PolicyTestGenerator();

        $testGenerator->setConfig($config = Mockery::mock(Config::class));
        $config->shouldReceive(['automaticGeneration' => false]);
        $this->assertFalse($this->callProtectedMethod($testGenerator, 'isTestable', $class, $reflectionMethod));

        $testGenerator->setConfig($config = Mockery::mock(Config::class));
        $config->shouldReceive(['automaticGeneration' => true]);
        $this->assertTrue($this->callProtectedMethod($testGenerator, 'isTestable', $class, $reflectionMethod));
    }

    public function testAddProperties(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $class = Mockery::mock(TestClass::class);
        $userImport = Mockery::mock(TestImport::class);
        $classProperty = Mockery::mock(TestProperty::class);
        $userDoc = Mockery::mock(TestDocumentation::class);
        $propertyFactory = Mockery::mock(PropertyFactoryContract::class);
        $config = Mockery::mock(Config::class);
        $documentationFactory = Mockery::mock(DocumentationFactoryContract::class);
        $importFactory = Mockery::mock(ImportFactoryContract::class);

        $testGenerator = new PolicyTestGenerator();
        $testGenerator->setPropertyFactory($propertyFactory);
        $testGenerator->setConfig($config);
        $testGenerator->setDocumentationFactory($documentationFactory);
        $testGenerator->setImportFactory($importFactory);

        $propertyFactory->shouldReceive('makeForClass')
            ->with($class)
            ->andReturn($classProperty);

        $reflectionClass->shouldReceive(['getImmediateMethods' => []]);

        $config->shouldReceive('getOption')
            ->with('laravel.user', 'App\\User')
            ->andReturn('App\\Models\\User');

        $importFactory->shouldReceive('make')
            ->with($class, 'App\\Models\\User')
            ->andReturn($userImport);

        $documentationFactory->shouldReceive('makeForProperty')
            ->with(Mockery::type(TestProperty::class), $userImport)
            ->andReturn($userDoc);

        $class->shouldReceive(['getReflectionClass' => $reflectionClass]);
        $class->shouldReceive('addProperty')
            ->with($classProperty);
        $class->shouldReceive('addProperty')
            ->with(Mockery::on(function (TestProperty $property) use ($userDoc) {
                return $property->getName() === 'user'
                    && $property->getDocumentation() === $userDoc;
            }));

        $this->callProtectedMethod($testGenerator, 'addProperties', $class);
    }
}
