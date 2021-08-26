<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Laravel;

use Mockery;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Generators\Tests\Laravel\UnitClassFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class UnitClassFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\Laravel\UnitClassFactory
 */
class UnitClassFactoryTest extends TestCase
{
    public function testNamespaceDefinitionIsExtended(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $config = Mockery::mock(Config::class);
        $documentationFactory = Mockery::mock(DocumentationFactory::class);
        $classFactory = new UnitClassFactory();
        $classFactory->setConfig($config);
        $classFactory->setDocumentationFactory($documentationFactory);

        $reflectionClass->shouldReceive([
            'getName' => 'PhpUnitGen\\App\\Foo\\Bar',
        ]);

        $config->shouldReceive([
            'baseNamespace'     => 'PhpUnitGen',
            'baseTestNamespace' => 'Tests',
        ]);

        $documentationFactory->shouldReceive('makeForClass')
            ->with(Mockery::type(TestClass::class))
            ->andReturn(Mockery::mock(TestDocumentation::class));

        $class = $classFactory->make($reflectionClass);

        $this->assertSame('Tests\\Unit\\App\\Foo\\BarTest', $class->getName());
    }
}
