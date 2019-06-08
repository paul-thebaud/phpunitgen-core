<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Parsers;

use Mockery;
use PhpUnitGen\Core\Generators\Concerns\UsesImports;
use PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class UsesImportsTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Concerns\UsesImports
 */
class UsesImportsTest extends TestCase
{
    /**
     * @var TestClass $class
     */
    protected $class;

    /**
     * @var UsesImports $usesImports
     */
    protected $usesImports;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->usesImports = new MockeryMockGenerator();

        $this->class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
    }

    public function testItReturnsAlreadyImportedClassWithoutAlias(): void
    {
        new TestImport($this->class, 'Foo\\Bar\\Baz');

        $class = $this->callProtectedMethod(
            $this->usesImports,
            'importClass',
            $this->class,
            'Foo\\Bar\\Baz'
        );

        $this->assertSame('Baz', $class);
        $this->assertCount(1, $this->class->getImports());
    }

    public function testItReturnsAlreadyImportedClassWithAlias(): void
    {
        new TestImport($this->class, 'Foo\\Bar\\Baz', 'BazAlias');

        $class = $this->callProtectedMethod(
            $this->usesImports,
            'importClass',
            $this->class,
            'Foo\\Bar\\Baz'
        );

        $this->assertSame('BazAlias', $class);
        $this->assertCount(1, $this->class->getImports());
    }

    public function testItReturnsNotImportedClassWithoutAliasing(): void
    {
        $class = $this->callProtectedMethod(
            $this->usesImports,
            'importClass',
            $this->class,
            'Foo\\Bar\\Baz'
        );

        $this->assertSame('Baz', $class);
        $this->assertCount(1, $this->class->getImports());
    }

    public function testItReturnsNotImportedClassWithAliasing(): void
    {
        new TestImport($this->class, 'Foo\\Bar\\BazModel', 'Baz');

        $class = $this->callProtectedMethod(
            $this->usesImports,
            'importClass',
            $this->class,
            'Foo\\Bar\\Baz'
        );

        $this->assertSame('BazAlias', $class);
        $this->assertCount(2, $this->class->getImports());
    }

    public function testItReturnsNotImportedClassWithDeepAliasing(): void
    {
        new TestImport($this->class, 'Foo\\Bar\\BazFoo', 'Baz');
        new TestImport($this->class, 'Foo\\Bar\\BazBar', 'BazAlias');

        $class = $this->callProtectedMethod(
            $this->usesImports,
            'importClass',
            $this->class,
            'Foo\\Bar\\Baz'
        );

        $this->assertSame('BazAliasAlias', $class);
        $this->assertCount(3, $this->class->getImports());
    }
}
