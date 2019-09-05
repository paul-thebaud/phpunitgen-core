<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Parsers;

use Mockery;
use PhpUnitGen\Core\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class ImportFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Factories\ImportFactory
 */
class ImportFactoryTest extends TestCase
{
    /**
     * @var TestClass
     */
    protected $class;

    /**
     * @var ImportFactory
     */
    protected $importFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importFactory = new ImportFactory();

        $this->class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
    }

    public function testItReturnsAlreadyImportedClassWithoutAlias(): void
    {
        $this->class->addImport(new TestImport('Foo\\Bar\\Baz'));

        $import = $this->importFactory->make($this->class, 'Foo\\Bar\\Baz');

        $this->assertSame('Baz', $import->getFinalName());
        $this->assertCount(1, $this->class->getImports());
    }

    public function testItReturnsAlreadyImportedClassWithAlias(): void
    {
        $this->class->addImport(new TestImport('Foo\\Bar\\Baz', 'BazAlias'));

        $import = $this->importFactory->make($this->class, 'Foo\\Bar\\Baz');

        $this->assertSame('BazAlias', $import->getFinalName());
        $this->assertCount(1, $this->class->getImports());
    }

    public function testItReturnsNotImportedClassWithoutAliasing(): void
    {
        $import = $this->importFactory->make($this->class, 'Foo\\Bar\\Baz');

        $this->assertSame('Baz', $import->getFinalName());
        $this->assertCount(1, $this->class->getImports());
    }

    public function testItReturnsNotImportedClassWithAliasing(): void
    {
        $this->class->addImport(new TestImport('Foo\\Bar\\BazModel', 'Baz'));

        $import = $this->importFactory->make($this->class, 'Foo\\Bar\\Baz');

        $this->assertSame('BazAlias', $import->getFinalName());
        $this->assertCount(2, $this->class->getImports());
    }

    public function testItReturnsNotImportedClassWithDeepAliasing(): void
    {
        $this->class->addImport(new TestImport('Foo\\Bar\\BazFoo', 'Baz'));
        $this->class->addImport(new TestImport('Foo\\Bar\\BazBar', 'BazAlias'));

        $import = $this->importFactory->make($this->class, 'Foo\\Bar\\Baz');

        $this->assertSame('BazAliasAlias', $import->getFinalName());
        $this->assertCount(3, $this->class->getImports());
    }
}
