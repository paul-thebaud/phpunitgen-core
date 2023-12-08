<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Laravel\Channel;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Factories\StatementFactory as StatementFactoryImpl;
use PhpUnitGen\Core\Generators\Tests\Laravel\Channel\ChannelMethodFactory;
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
 * Class ChannelMethodFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\Laravel\Channel\ChannelMethodFactory
 */
class ChannelMethodFactoryTest extends TestCase
{
    /**
     * @var Config|Mock
     */
    protected $config;

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
     * @var ChannelMethodFactory
     */
    protected $methodFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(Config::class);
        $this->documentationFactory = Mockery::mock(DocumentationFactory::class);
        $this->importFactory = Mockery::mock(ImportFactory::class);
        $this->statementFactory = Mockery::mock(StatementFactory::class);
        $this->valueFactory = Mockery::mock(ValueFactory::class);
        $this->methodFactory = new ChannelMethodFactory();
        $this->methodFactory->setConfig($this->config);
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

        $this->config->shouldReceive('getOption')
            ->with('laravel.user', 'App\\User')
            ->andReturn('App\\User');

        $this->importFactory->shouldReceive('make')
            ->with($class, 'App\\User')
            ->andReturn(new TestImport('App\\User'));

        $this->statementFactory->shouldReceive('makeTodo')
            ->with('Correctly instantiate tested object to use it.')
            ->andReturn(new TestStatement('/** @todo Correctly instantiate tested object to use it. */'));
        $this->statementFactory->shouldReceive('makeInstantiation')
            ->with($class, Mockery::on(function ($value) {
                return $value instanceof Collection && $value->isEmpty();
            }))
            ->andReturn(new TestStatement('$this->foo = new Foo()'));
        $this->statementFactory->shouldReceive('makeAffect')
            ->with('user', 'new User()')
            ->andReturn(new TestStatement('$this->user = new User()'));

        $method = $this->methodFactory->makeSetUp($class);

        self::assertSame([
            ['parent::setUp()'],
            [''],
            ['/** @todo Correctly instantiate tested object to use it. */'],
            ['$this->foo = new Foo()'],
            ['$this->user = new User()'],
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
        $this->expectExceptionMessage('cannot generate tests for method bar, not a "join" method');

        $this->methodFactory->makeTestable($class, $reflectionMethod);
    }

    public function testMakeTestableJoinMethod(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $class = new TestClass($reflectionClass, 'App\\FooTest');

        $reflectionClass->shouldReceive([
            'getShortName'           => 'Foo',
            'getImmediateProperties' => [],
        ]);

        $reflectionMethod->shouldReceive([
            'getShortName'      => 'join',
            'getDeclaringClass' => $class->getReflectionClass(),
            'isStatic'          => false,
        ]);

        $this->statementFactory->shouldReceive('makeTodo')
            ->twice()
            ->with('This test is incomplete.')
            ->andReturn(new TestStatement('/** @todo This test is incomplete. */'));
        $this->statementFactory->shouldReceive('makeAssert')
            ->once()
            ->with('false', '$this->foo->join($this->user)')
            ->andReturn(new TestStatement('self::assertFalse($this->foo->join($this->user))'));
        $this->statementFactory->shouldReceive('makeAssert')
            ->once()
            ->with('true', '$this->foo->join($this->user)')
            ->andReturn(new TestStatement('self::assertTrue($this->foo->join($this->user))'));

        $this->methodFactory->makeTestable($class, $reflectionMethod);

        $method1 = $class->getMethods()[0];

        self::assertSame('testJoinWhenUnauthorized', $method1->getName());
        self::assertSame('public', $method1->getVisibility());
        self::assertNull($method1->getDocumentation());
        self::assertSame([
            ['/** @todo This test is incomplete. */'],
            ['self::assertFalse($this->foo->join($this->user))'],
        ], $method1->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());

        $method2 = $class->getMethods()[1];

        self::assertSame('testJoinWhenAuthorized', $method2->getName());
        self::assertSame('public', $method2->getVisibility());
        self::assertNull($method2->getDocumentation());
        self::assertSame([
            ['/** @todo This test is incomplete. */'],
            ['self::assertTrue($this->foo->join($this->user))'],
        ], $method2->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }
}
