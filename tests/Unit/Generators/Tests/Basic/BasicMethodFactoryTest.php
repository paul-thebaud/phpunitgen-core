<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Basic;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory as StatementFactoryContract;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Factories\StatementFactory;
use PhpUnitGen\Core\Generators\Tests\Basic\BasicMethodFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class BasicMethodFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\Basic\BasicMethodFactory
 * @covers \PhpUnitGen\Core\Generators\Tests\Basic\ManagesGetterAndSetter
 */
class BasicMethodFactoryTest extends TestCase
{
    /**
     * @var ImportFactory|Mock
     */
    protected $importFactory;

    /**
     * @var StatementFactoryContract
     */
    protected $statementFactory;

    /**
     * @var ValueFactory|Mock
     */
    protected $valueFactory;

    /**
     * @var BasicMethodFactory
     */
    protected $methodFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importFactory = Mockery::mock(ImportFactory::class);
        $this->statementFactory = new StatementFactory();
        $this->valueFactory = Mockery::mock(ValueFactory::class);
        $this->methodFactory = new BasicMethodFactory();
        $this->methodFactory->setImportFactory($this->importFactory);
        $this->methodFactory->setStatementFactory($this->statementFactory);
        $this->methodFactory->setValueFactory($this->valueFactory);
    }

    public function testMakeTestableWillThrowException(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'getShortName'           => 'Foo',
            'getImmediateProperties' => [],
        ]);

        $reflectionMethod->shouldReceive([
            'getShortName'      => 'bar',
            'getDeclaringClass' => $class->getReflectionClass(),
            'isStatic'          => false,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'cannot generate tests for method bar, not a getter or a setter'
        );

        $this->methodFactory->makeTestable($class, $reflectionMethod);
    }

    /**
     * @param array $expectedStatements
     * @param bool  $isPublic
     * @param bool  $isStatic
     *
     * @dataProvider makeTestableForGetterDataProvider
     */
    public function testMakeTestableForGetter(array $expectedStatements, bool $isPublic, bool $isStatic): void
    {
        $reflectionProperty = Mockery::mock(ReflectionProperty::class);
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'getShortName'           => 'Foo',
            'getImmediateProperties' => [$reflectionProperty],
        ]);

        $reflectionMethod->shouldReceive([
            'getShortName'      => 'getBar',
            'getDeclaringClass' => $class->getReflectionClass(),
            'getReturnType'     => null,
            'getDocComment'     => '',
            'isStatic'          => $isStatic,
        ]);

        $reflectionProperty->shouldReceive([
            'getName'  => 'bar',
            'isPublic' => $isPublic,
            'isStatic' => $isStatic,
        ]);

        $this->importFactory->shouldReceive('make')
            ->with($class, 'ReflectionClass')
            ->andReturn(new TestImport('ReflectionClass'));

        $this->valueFactory->shouldReceive('make')
            ->with($class, null)
            ->andReturn('null');

        $this->methodFactory->makeTestable($class, $reflectionMethod);

        $method = $class->getMethods()[0];

        self::assertSame('testGetBar', $method->getName());
        self::assertSame('public', $method->getVisibility());
        self::assertNull($method->getDocumentation());
        self::assertSame($expectedStatements, $method->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }

    public static function makeTestableForGetterDataProvider(): array
    {
        return [
            [
                [
                    ['$expected = null'],
                    ['$property = (new ReflectionClass(Foo::class))', '->getProperty(\'bar\')'],
                    ['$property->setValue($this->foo, $expected)'],
                    ['self::assertSame($expected, $this->foo->getBar())'],
                ],
                true,
                false,
            ],
            [
                [
                    ['$expected = null'],
                    ['$property = (new ReflectionClass(Foo::class))', '->getProperty(\'bar\')'],
                    ['$property->setValue(null, $expected)'],
                    ['self::assertSame($expected, Foo::getBar())'],
                ],
                true,
                true,
            ],
            [
                [
                    ['$expected = null'],
                    ['$property = (new ReflectionClass(Foo::class))', '->getProperty(\'bar\')'],
                    ['$property->setValue(null, $expected)'],
                    ['self::assertSame($expected, Foo::getBar())'],
                ],
                false,
                true,
            ],
            [
                [
                    ['$expected = null'],
                    ['$property = (new ReflectionClass(Foo::class))', '->getProperty(\'bar\')'],
                    ['$property->setValue($this->foo, $expected)'],
                    ['self::assertSame($expected, $this->foo->getBar())'],
                ],
                false,
                false,
            ],
        ];
    }

    /**
     * @param array $expectedStatements
     * @param bool  $isPublic
     * @param bool  $isStatic
     *
     * @dataProvider makeTestableForSetterDataProvider
     */
    public function testMakeTestableForSetter(array $expectedStatements, bool $isPublic, bool $isStatic): void
    {
        $reflectionProperty = Mockery::mock(ReflectionProperty::class);
        $reflectionParameter = Mockery::mock(ReflectionParameter::class);
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'getShortName'           => 'Foo',
            'getImmediateProperties' => [$reflectionProperty],
        ]);

        $reflectionMethod->shouldReceive([
            'getShortName'      => 'setBar',
            'getDeclaringClass' => $class->getReflectionClass(),
            'getParameters'     => [$reflectionParameter],
            'getDocComment'     => '',
            'isStatic'          => $isStatic,
        ]);

        $reflectionParameter->shouldReceive([
            'getType'              => null,
            'getDeclaringFunction' => $reflectionMethod,
        ]);

        $reflectionProperty->shouldReceive([
            'getName'  => 'bar',
            'isPublic' => $isPublic,
            'isStatic' => $isStatic,
        ]);

        $this->importFactory->shouldReceive('make')
            ->with($class, 'ReflectionClass')
            ->andReturn(new TestImport('ReflectionClass'));

        $this->valueFactory->shouldReceive('make')
            ->with($class, null)
            ->andReturn('null');

        $this->methodFactory->makeTestable($class, $reflectionMethod);

        $method = $class->getMethods()[0];

        self::assertSame('testSetBar', $method->getName());
        self::assertSame('public', $method->getVisibility());
        self::assertNull($method->getDocumentation());
        self::assertSame($expectedStatements, $method->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }

    public static function makeTestableForSetterDataProvider(): array
    {
        return [
            [
                [
                    ['$expected = null'],
                    ['$property = (new ReflectionClass(Foo::class))', '->getProperty(\'bar\')'],
                    ['$this->foo->setBar($expected)'],
                    ['self::assertSame($expected, $property->getValue($this->foo))'],
                ],
                true,
                false,
            ],
            [
                [
                    ['$expected = null'],
                    ['$property = (new ReflectionClass(Foo::class))', '->getProperty(\'bar\')'],
                    ['Foo::setBar($expected)'],
                    ['self::assertSame($expected, $property->getValue(null))'],
                ],
                true,
                true,
            ],
            [
                [
                    ['$expected = null'],
                    ['$property = (new ReflectionClass(Foo::class))', '->getProperty(\'bar\')'],
                    ['Foo::setBar($expected)'],
                    ['self::assertSame($expected, $property->getValue(null))'],
                ],
                false,
                true,
            ],
            [
                [
                    ['$expected = null'],
                    ['$property = (new ReflectionClass(Foo::class))', '->getProperty(\'bar\')'],
                    ['$this->foo->setBar($expected)'],
                    ['self::assertSame($expected, $property->getValue($this->foo))'],
                ],
                false,
                false,
            ],
        ];
    }
}
