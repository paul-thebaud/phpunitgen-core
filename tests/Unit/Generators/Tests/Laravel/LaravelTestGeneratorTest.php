<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Laravel;

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
use PhpUnitGen\Core\Generators\Tests\Basic\BasicMethodFactory;
use PhpUnitGen\Core\Generators\Tests\Laravel\LaravelTestGenerator;
use PhpUnitGen\Core\Generators\Tests\Laravel\UnitClassFactory;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class LaravelTestGeneratorTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\Laravel\LaravelTestGenerator
 */
class LaravelTestGeneratorTest extends TestCase
{
    public function testImplementations(): void
    {
        $implementations = LaravelTestGenerator::implementations();

        $this->assertSame([
            TestGeneratorContract::class        => LaravelTestGenerator::class,
            ClassFactoryContract::class         => UnitClassFactory::class,
            DocumentationFactoryContract::class => DocumentationFactory::class,
            ImportFactoryContract::class        => ImportFactory::class,
            MethodFactoryContract::class        => BasicMethodFactory::class,
            PropertyFactoryContract::class      => PropertyFactory::class,
            StatementFactoryContract::class     => StatementFactory::class,
            ValueFactoryContract::class         => ValueFactory::class,
        ], $implementations);

        foreach ($implementations as $contract => $implementation) {
            $this->assertArrayHasKey($contract, class_implements($implementation));
        }
    }
}
