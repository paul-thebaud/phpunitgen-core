<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Laravel;

use Mockery;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Generators\Tests\Laravel\FeatureClassFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class FeatureClassFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\Laravel\FeatureClassFactory
 */
class FeatureClassFactoryTest extends TestCase
{
    public function testNamespaceDefinitionIsExtended(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $config = Mockery::mock(Config::class);
        $documentationFactory = Mockery::mock(DocumentationFactory::class);
        $classFactory = new FeatureClassFactory();
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

        $this->assertSame('Tests\\Feature\\App\\Foo\\BarTest', $class->getName());
    }
}
