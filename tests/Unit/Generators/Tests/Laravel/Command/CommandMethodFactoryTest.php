<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Laravel\Command;

use Error;
use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Factories\StatementFactory as StatementFactoryImpl;
use PhpUnitGen\Core\Generators\Tests\Laravel\Command\CommandMethodFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Tests\PhpUnitGen\Core\TestCase;
use Tightenco\Collect\Support\Collection;

/**
 * Class CommandMethodFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\Laravel\Command\CommandMethodFactory
 */
class CommandMethodFactoryTest extends TestCase
{
    /**
     * @var DocumentationFactory|Mock
     */
    protected $documentationFactory;

    /**
     * @var ImportFactory|Mock
     */
    protected $importFactory;

    /**
     * @var StatementFactory|Mock
     */
    protected $statementFactory;

    /**
     * @var ValueFactory|Mock
     */
    protected $valueFactory;

    /**
     * @var CommandMethodFactory
     */
    protected $methodFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->documentationFactory = Mockery::mock(DocumentationFactory::class);
        $this->importFactory = Mockery::mock(ImportFactory::class);
        $this->statementFactory = Mockery::mock(StatementFactory::class);
        $this->valueFactory = Mockery::mock(ValueFactory::class);
        $this->methodFactory = new CommandMethodFactory();
        $this->methodFactory->setDocumentationFactory($this->documentationFactory);
        $this->methodFactory->setImportFactory($this->importFactory);
        $this->methodFactory->setStatementFactory($this->statementFactory);
        $this->methodFactory->setValueFactory($this->valueFactory);
    }

    public function testMakeSetUp(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive([
            'getShortName'           => 'Foo',
            'getImmediateMethods'    => [],
            'getImmediateProperties' => [],
        ]);

        $this->documentationFactory->shouldReceive([
            'makeForInheritedMethod' => Mockery::mock(TestDocumentation::class),
        ]);

        $this->statementFactory->shouldReceive('makeTodo')
            ->with('Correctly instantiate tested object to use it.')
            ->andReturn(new TestStatement('/** @todo Correctly instantiate tested object to use it. */'));
        $this->statementFactory->shouldReceive('makeInstantiation')
            ->with($class, Mockery::on(function ($value) {
                return $value instanceof Collection && $value->isEmpty();
            }))
            ->andReturn(new TestStatement('$this->foo = new Foo()'));

        $method = $this->methodFactory->makeSetUp($class);

        self::assertSame([
            ['parent::setUp()'],
            [''],
            ['/** @todo Correctly instantiate tested object to use it. */'],
            ['$this->foo = new Foo()'],
            ['$this->app->instance(Foo::class, $this->foo)'],
        ], $method->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }

    public function testMakeTestableWithGetter(): void
    {
        $this->methodFactory->setStatementFactory(new StatementFactoryImpl());

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
            'isStatic'          => false,
        ]);

        $reflectionProperty->shouldReceive([
            'getName'  => 'bar',
            'isPublic' => true,
            'isStatic' => false,
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
        self::assertSame([
            ['$expected = null'],
            ['$property = (new ReflectionClass(Foo::class))', '->getProperty(\'bar\')'],
            ['$property->setValue($this->foo, $expected)'],
            ['self::assertSame($expected, $this->foo->getBar())'],
        ], $method->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }

    public function testMakeTestableForStatic(): void
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
            'getReturnType'     => null,
            'isStatic'          => true,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot generate tests for method bar, not a "handle" method');

        $this->methodFactory->makeTestable($class, $reflectionMethod);
    }

    /**
     * @param string $expectedSignature
     * @param array  $properties
     *
     * @dataProvider makeTestableHandleMethodDataProvider
     */
    public function testMakeTestableHandleMethod(string $expectedSignature, array $properties): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $class = new TestClass($reflectionClass, 'App\\FooTest');

        $reflectionClass->shouldReceive([
            'getShortName'           => 'Foo',
            'getImmediateProperties' => $properties,
        ]);

        $reflectionMethod->shouldReceive([
            'getShortName'      => 'handle',
            'getDeclaringClass' => $class->getReflectionClass(),
            'isStatic'          => false,
        ]);

        $this->statementFactory->shouldReceive('makeTodo')
            ->once()
            ->with('This test is incomplete.')
            ->andReturn(new TestStatement('/** @todo This test is incomplete. */'));

        $this->methodFactory->makeTestable($class, $reflectionMethod);

        $method = $class->getMethods()[0];

        self::assertSame('testHandle', $method->getName());
        self::assertSame('public', $method->getVisibility());
        self::assertNull($method->getDocumentation());
        self::assertSame([
            ['/** @todo This test is incomplete. */'],
            [
                "\$this->artisan('{$expectedSignature}')",
                '->expectsOutput(\'Some expected output\')',
                '->assertExitCode(0)',
            ],
        ], $method->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }

    public static function makeTestableHandleMethodDataProvider(): array
    {
        $invalidName = Mockery::mock(ReflectionProperty::class);
        $static = Mockery::mock(ReflectionProperty::class);
        $notProtected = Mockery::mock(ReflectionProperty::class);
        $invalidValue = Mockery::mock(ReflectionProperty::class);
        $errorValue = Mockery::mock(ReflectionProperty::class);
        $correctValue = Mockery::mock(ReflectionProperty::class);

        $invalidName->shouldReceive([
            'getName' => 'notSignature',
        ]);
        $static->shouldReceive([
            'getName'  => 'signature',
            'isStatic' => true,
        ]);
        $notProtected->shouldReceive([
            'getName'     => 'signature',
            'isStatic'    => false,
            'isProtected' => false,
        ]);
        $invalidValue->shouldReceive([
            'getName'         => 'signature',
            'isStatic'        => false,
            'isProtected'     => true,
            'getDefaultValue' => 42,
        ]);
        $errorValue->shouldReceive([
            'getName'     => 'signature',
            'isStatic'    => false,
            'isProtected' => true,
        ]);
        $errorValue->shouldReceive('getDefaultValue')->andThrow(new Error());
        $correctValue->shouldReceive([
            'getName'         => 'signature',
            'isStatic'        => false,
            'isProtected'     => true,
            'getDefaultValue' => 'foo:bar',
        ]);

        return [
            ['command:name', []],
            ['command:name', [$invalidName]],
            ['command:name', [$static]],
            ['command:name', [$notProtected]],
            ['command:name', [$invalidValue]],
            ['command:name', [$errorValue]],
            ['foo:bar', [$correctValue]],
        ];
    }
}
