<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Factories;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Factories\TypeFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;
use Tightenco\Collect\Support\Collection;

/**
 * Class TypeFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Factories\TypeFactory
 */
class TypeFactoryTest extends TestCase
{
    /**
     * @var ImportFactory|Mock
     */
    protected $importFactory;

    /**
     * @var TypeFactory
     */
    protected $typeFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importFactory = Mockery::mock(ImportFactory::class);
        $this->typeFactory = new TypeFactory();
        $this->typeFactory->setImportFactory($this->importFactory);
    }

    public function testFormatType(): void
    {
        $builtInType = 'string';
        $importType = new TestImport('DummyClass');

        self::assertSame('string', $this->typeFactory->formatType($builtInType));
        self::assertSame('DummyClass', $this->typeFactory->formatType($importType));
    }

    public function testFormatTypes(): void
    {
        $builtInType = 'string';
        $importType = new TestImport('DummyClass');

        self::assertSame(
            'string|DummyClass',
            $this->typeFactory->formatTypes(new Collection([$builtInType, $importType]))
        );
        self::assertSame(
            'DummyClass|string',
            $this->typeFactory->formatTypes(new Collection([$importType, $builtInType]))
        );
        self::assertSame(
            'DummyClass&string',
            $this->typeFactory->formatTypes(new Collection([$importType, $builtInType]), '&')
        );
    }

    /**
     * @param TestImport|string $expectedType
     * @param string|null       $expectedImport
     * @param string            $type
     * @param bool              $isBuiltIn
     *
     * @dataProvider makeFromStringDataProvider
     */
    public function testMakeFromString(
        TestImport|string $expectedType,
        string|null $expectedImport,
        string $type,
        bool $isBuiltIn
    ): void {
        $class = Mockery::mock(TestClass::class);
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $class->shouldReceive('getReflectionClass')
            ->zeroOrMoreTimes()
            ->andReturn($reflectionClass);
        $reflectionClass->shouldReceive('getName')
            ->zeroOrMoreTimes()
            ->andReturn('DummyTestClass');

        if ($expectedImport) {
            $this->importFactory->shouldReceive('make')
                ->once()
                ->with($class, $expectedImport)
                ->andReturn($expectedType);
        }

        self::assertSame($expectedType, $this->typeFactory->makeFromString($class, $type, $isBuiltIn));
    }

    public static function makeFromStringDataProvider(): array
    {
        return [
            [new TestImport('MyParentClass'), 'DummyTestClass', 'parent', true],
            [new TestImport('MyParentClass'), 'DummyTestClass', 'self', true],
            [new TestImport('MyParentClass'), 'DummyTestClass', 'static', true],
            ['int', null, 'int', true],
            ['bool', null, 'bool', true],
            ['mixed', null, 'mixed', true],
            [new TestImport('mixed'), 'mixed', 'mixed', false],
            [new TestImport('AnotherClass'), 'AnotherClass', 'AnotherClass', false],
        ];
    }
}
