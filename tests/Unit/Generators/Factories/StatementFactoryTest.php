<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Factories;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Factories\StatementFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Tests\PhpUnitGen\Core\TestCase;
use Tightenco\Collect\Support\Collection;

/**
 * Class StatementFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Factories\StatementFactory
 */
class StatementFactoryTest extends TestCase
{
    /**
     * @var ImportFactory|Mock
     */
    protected $importFactory;

    /**
     * @var StatementFactory
     */
    protected $statementFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importFactory = Mockery::mock(ImportFactory::class);
        $this->statementFactory = new StatementFactory();
        $this->statementFactory->setImportFactory($this->importFactory);
    }

    public function testMakeTodo(): void
    {
        $statement = $this->statementFactory->makeTodo('This is a TODO.');

        $this->assertSame([
            '/** @todo This is a TODO. */',
        ], $statement->getLines()->toArray());
    }

    /**
     * @param string $expected
     * @param string $name
     * @param string $value
     * @param bool   $isProperty
     *
     * @dataProvider makeAffectDataProvider
     */
    public function testMakeAffect(string $expected, string $name, string $value, bool $isProperty): void
    {
        $statement = $this->statementFactory->makeAffect($name, $value, $isProperty);

        $this->assertSame([
            $expected,
        ], $statement->getLines()->toArray());
    }

    public function makeAffectDataProvider(): array
    {
        return [
            ['$this->foo = 1', 'foo', '1', true],
            ['$foo = 1', 'foo', '1', false],
        ];
    }

    public function testMakeAssertWithoutParameters(): void
    {
        $statement = $this->statementFactory->makeAssert('equals');

        $this->assertSame([
            '$this->assertEquals()',
        ], $statement->getLines()->toArray());
    }

    public function testMakeAssertWithParameters(): void
    {
        $statement = $this->statementFactory->makeAssert('Equals', '1', '2');

        $this->assertSame([
            '$this->assertEquals(1, 2)',
        ], $statement->getLines()->toArray());
    }

    /**
     * @param array                $expectedStatementLines
     * @param ReflectionClass|Mock $reflectionClass
     * @param array                $parameters
     *
     * @dataProvider makeInstantiationDataProvider
     */
    public function testMakeInstantiation(
        array $expectedStatementLines,
        $reflectionClass,
        array $parameters
    ): void {
        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'getName'      => 'App\\Foo',
            'getShortName' => 'Foo',
        ]);

        $this->importFactory->shouldReceive('make')
            ->with($class, 'App\\Foo')
            ->andReturn(new TestImport('App\\Foo'));

        $statement = $this->statementFactory->makeInstantiation($class, new Collection($parameters));

        $this->assertSame($expectedStatementLines, $statement->getLines()->toArray());
    }

    public function makeInstantiationDataProvider(): array
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionAbstract = Mockery::mock(ReflectionClass::class);
        $reflectionTrait = Mockery::mock(ReflectionClass::class);
        $param1 = Mockery::mock(ReflectionParameter::class);
        $param2 = Mockery::mock(ReflectionParameter::class);
        $parameters = [$param1, $param2];

        $reflectionClass->shouldReceive(['isAbstract' => false, 'isTrait' => false]);
        $reflectionAbstract->shouldReceive(['isAbstract' => true, 'isTrait' => false]);
        $reflectionTrait->shouldReceive(['isAbstract' => false, 'isTrait' => true]);

        $param1->shouldReceive(['getName' => 'bar']);
        $param2->shouldReceive(['getName' => 'baz']);

        return [
            [['$this->foo = new Foo()'], $reflectionClass, []],
            [['$this->foo = new Foo($this->bar, $this->baz)'], $reflectionClass, $parameters],
            [
                [
                    '$this->foo = $this->getMockBuilder(Foo::class)',
                    '->setConstructorArgs([])',
                    '->getMockForAbstractClass()',
                ],
                $reflectionAbstract,
                [],
            ],
            [
                [
                    '$this->foo = $this->getMockBuilder(Foo::class)',
                    '->setConstructorArgs([$this->bar, $this->baz])',
                    '->getMockForAbstractClass()',
                ],
                $reflectionAbstract,
                $parameters,
            ],
            [
                [
                    '$this->foo = $this->getMockBuilder(Foo::class)',
                    '->setConstructorArgs([])',
                    '->getMockForTrait()',
                ],
                $reflectionTrait,
                [],
            ],
            [
                [
                    '$this->foo = $this->getMockBuilder(Foo::class)',
                    '->setConstructorArgs([$this->bar, $this->baz])',
                    '->getMockForTrait()',
                ],
                $reflectionTrait,
                $parameters,
            ],
        ];
    }
}
