<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Parsers;

use Mockery;
use PhpUnitGen\Core\Generators\Concerns\CreatesTestImports;
use PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class CreatesTestImportsTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Concerns\CreatesTestImports
 */
class CreatesTestImportsTest extends TestCase
{
    /**
     * @var TestClass
     */
    protected $class;

    /**
     * @var CreatesTestImports
     */
    protected $createsTestImports;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createsTestImports = new MockeryMockGenerator();

        $this->class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
    }

    public function testItReturnsAlreadyImportedClassWithoutAlias(): void
    {
        $this->class->addImport(new TestImport('Foo\\Bar\\Baz'));

        $class = $this->callProtectedMethod(
            $this->createsTestImports,
            'createTestImport',
            $this->class,
            'Foo\\Bar\\Baz'
        );

        $this->assertSame('Baz', $class);
        $this->assertCount(1, $this->class->getImports());
    }

    public function testItReturnsAlreadyImportedClassWithAlias(): void
    {
        $this->class->addImport(new TestImport('Foo\\Bar\\Baz', 'BazAlias'));

        $class = $this->callProtectedMethod(
            $this->createsTestImports,
            'createTestImport',
            $this->class,
            'Foo\\Bar\\Baz'
        );

        $this->assertSame('BazAlias', $class);
        $this->assertCount(1, $this->class->getImports());
    }

    public function testItReturnsNotImportedClassWithoutAliasing(): void
    {
        $class = $this->callProtectedMethod(
            $this->createsTestImports,
            'createTestImport',
            $this->class,
            'Foo\\Bar\\Baz'
        );

        $this->assertSame('Baz', $class);
        $this->assertCount(1, $this->class->getImports());
    }

    public function testItReturnsNotImportedClassWithAliasing(): void
    {
        $this->class->addImport(new TestImport('Foo\\Bar\\BazModel', 'Baz'));

        $class = $this->callProtectedMethod(
            $this->createsTestImports,
            'createTestImport',
            $this->class,
            'Foo\\Bar\\Baz'
        );

        $this->assertSame('BazAlias', $class);
        $this->assertCount(2, $this->class->getImports());
    }

    public function testItReturnsNotImportedClassWithDeepAliasing(): void
    {
        $this->class->addImport(new TestImport('Foo\\Bar\\BazFoo', 'Baz'));
        $this->class->addImport(new TestImport('Foo\\Bar\\BazBar', 'BazAlias'));

        $class = $this->callProtectedMethod(
            $this->createsTestImports,
            'createTestImport',
            $this->class,
            'Foo\\Bar\\Baz'
        );

        $this->assertSame('BazAliasAlias', $class);
        $this->assertCount(3, $this->class->getImports());
    }
}
