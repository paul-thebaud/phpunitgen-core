<?php

namespace Tests\PhpUnitGen\Core\Unit\Generators\Factories;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Generators\Factories\MethodFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use PhpUnitGen\Core\Models\TestStatement;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionType;
use Tests\PhpUnitGen\Core\TestCase;
use Tightenco\Collect\Support\Collection;

/**
 * Class MethodFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Factories\MethodFactory
 * @covers \PhpUnitGen\Core\Generators\Concerns\InstantiatesClass
 */
class MethodFactoryTest extends TestCase
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
     * @var MethodFactory
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
        $this->methodFactory = new MethodFactory();
        $this->methodFactory->setDocumentationFactory($this->documentationFactory);
        $this->methodFactory->setImportFactory($this->importFactory);
        $this->methodFactory->setStatementFactory($this->statementFactory);
        $this->methodFactory->setValueFactory($this->valueFactory);
    }

    /**
     * @param array $methods
     *
     * @dataProvider makeSetUpWithoutConstructorDataProvider
     */
    public function testMakeSetUpWithoutConstructor(array $methods): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $doc = Mockery::mock(TestDocumentation::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive('getShortName')
            ->withNoArgs()
            ->andReturn('Foo');
        $reflectionClass->shouldReceive('getImmediateMethods')
            ->withNoArgs()
            ->andReturn($methods);

        $this->documentationFactory->shouldReceive('makeForInheritedMethod')
            ->with(Mockery::type(TestMethod::class))
            ->andReturn($doc);

        $this->statementFactory->shouldReceive('makeTodo')
            ->with('Instantiate tested object to use it.')
            ->andReturn(new TestStatement('/** @todo Instantiate tested object to use it. */'));
        $this->statementFactory->shouldReceive('makeAffect')
            ->with('foo', 'null')
            ->andReturn(new TestStatement('$this->foo = null'));

        $method = $this->methodFactory->makeSetUp($class);

        $this->assertSame('setUp', $method->getName());
        $this->assertSame('protected', $method->getVisibility());
        $this->assertSame($doc, $method->getDocumentation());
        $this->assertSame([
            ['parent::setUp()'],
            [''],
            ['/** @todo Instantiate tested object to use it. */'],
            ['$this->foo = null'],
        ], $method->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }

    public function makeSetUpWithoutConstructorDataProvider(): array
    {
        $protectedConstructor = Mockery::mock(ReflectionMethod::class);
        $abstractConstructor = Mockery::mock(ReflectionMethod::class);
        $staticConstructor = Mockery::mock(ReflectionMethod::class);

        $protectedConstructor->shouldReceive([
            'getShortName' => '__constructor',
            'isPublic'     => false,
        ]);
        $abstractConstructor->shouldReceive([
            'getShortName' => '__constructor',
            'isPublic'     => true,
            'isAbstract'   => true,
        ]);
        $staticConstructor->shouldReceive([
            'getShortName' => '__constructor',
            'isPublic'     => true,
            'isAbstract'   => false,
            'isStatic'     => true,
        ]);

        return [
            [[]],
            [[$protectedConstructor]],
            [[$abstractConstructor]],
            [[$staticConstructor]],
        ];
    }

    public function testMakeSetUpWithConstructor(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionParameter1 = Mockery::mock(ReflectionParameter::class);
        $reflectionParameter2 = Mockery::mock(ReflectionParameter::class);
        $reflectionType = Mockery::mock(ReflectionType::class);
        $doc = Mockery::mock(TestDocumentation::class);

        $class = new TestClass($reflectionClass, 'FooTest');

        $reflectionClass->shouldReceive('getShortName')
            ->withNoArgs()
            ->andReturn('Foo');
        $reflectionClass->shouldReceive('getImmediateMethods')
            ->withNoArgs()
            ->andReturn([$reflectionMethod]);

        $reflectionMethod->shouldReceive([
            'getShortName'  => '__construct',
            'isPublic'      => true,
            'isAbstract'    => false,
            'isStatic'      => false,
            'getParameters' => [$reflectionParameter1, $reflectionParameter2],
        ]);

        $reflectionParameter1->shouldReceive([
            'getType' => null,
            'getName' => 'bar',
        ]);
        $reflectionParameter2->shouldReceive([
            'getType' => $reflectionType,
            'getName' => 'baz',
        ]);

        $this->documentationFactory->shouldReceive('makeForInheritedMethod')
            ->with(Mockery::type(TestMethod::class))
            ->andReturn($doc);

        $this->valueFactory->shouldReceive('make')
            ->with($class, null)
            ->andReturn('null');
        $this->valueFactory->shouldReceive('make')
            ->with($class, $reflectionType)
            ->andReturn('42');

        $this->statementFactory->shouldReceive('makeAffect')
            ->with('bar', 'null')
            ->andReturn(new TestStatement('$this->bar = null'));
        $this->statementFactory->shouldReceive('makeAffect')
            ->with('baz', '42')
            ->andReturn(new TestStatement('$this->baz = 42'));
        $this->statementFactory->shouldReceive('makeInstantiation')
            ->with($class, Mockery::type(Collection::class))
            ->andReturn(new TestStatement('$this->foo = new Foo($this->bar, $this->baz)'));

        $method = $this->methodFactory->makeSetUp($class);

        $this->assertSame('setUp', $method->getName());
        $this->assertSame('protected', $method->getVisibility());
        $this->assertSame($doc, $method->getDocumentation());
        $this->assertSame([
            ['parent::setUp()'],
            [''],
            ['$this->bar = null'],
            ['$this->baz = 42'],
            ['$this->foo = new Foo($this->bar, $this->baz)'],
        ], $method->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }

    public function testMakeTearDownWithProperties(): void
    {
        $doc = Mockery::mock(TestDocumentation::class);

        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');
        $class->addProperty(new TestProperty('foo'));
        $class->addProperty(new TestProperty('bar'));

        $this->documentationFactory->shouldReceive('makeForInheritedMethod')
            ->with(Mockery::type(TestMethod::class))
            ->andReturn($doc);

        $method = $this->methodFactory->makeTearDown($class);

        $this->assertSame('tearDown', $method->getName());
        $this->assertSame('protected', $method->getVisibility());
        $this->assertSame($doc, $method->getDocumentation());
        $this->assertSame([
            ['parent::tearDown()'],
            [''],
            ['unset($this->foo)'],
            ['unset($this->bar)'],
        ], $method->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }

    public function testMakeTearDownWithoutProperties(): void
    {
        $doc = Mockery::mock(TestDocumentation::class);

        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');

        $this->documentationFactory->shouldReceive('makeForInheritedMethod')
            ->with(Mockery::type(TestMethod::class))
            ->andReturn($doc);

        $this->statementFactory->shouldReceive('makeTodo')
            ->with('Complete the tearDown() method.')
            ->andReturn(new TestStatement('/** @todo Complete the tearDown() method. */'));

        $method = $this->methodFactory->makeTearDown($class);

        $this->assertSame('tearDown', $method->getName());
        $this->assertSame('protected', $method->getVisibility());
        $this->assertSame($doc, $method->getDocumentation());
        $this->assertSame([
            ['parent::tearDown()'],
            [''],
            ['/** @todo Complete the tearDown() method. */'],
        ], $method->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }

    public function testMakeEmpty(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $reflectionMethod->shouldReceive('getShortName')
            ->withNoArgs()
            ->andReturn('fooBar');

        $method = $this->methodFactory->makeEmpty($reflectionMethod, 'UsingBaz');

        $this->assertSame('testFooBarUsingBaz', $method->getName());
        $this->assertSame('public', $method->getVisibility());
    }

    public function testMakeIncomplete(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $reflectionMethod->shouldReceive('getShortName')
            ->withNoArgs()
            ->andReturn('fooBar');

        $this->statementFactory->shouldReceive('makeTodo')
            ->with('This test is incomplete.')
            ->andReturn(new TestStatement('/** @todo This test is incomplete. */'));

        $method = $this->methodFactory->makeIncomplete($reflectionMethod);

        $this->assertSame('testFooBar', $method->getName());
        $this->assertSame('public', $method->getVisibility());
        $this->assertSame([
            ['/** @todo This test is incomplete. */'],
            ['$this->markTestIncomplete()'],
        ], $method->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }

    public function testMakeTestable(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest');

        $reflectionMethod->shouldReceive('getShortName')
            ->withNoArgs()
            ->andReturn('fooBar');

        $this->statementFactory->shouldReceive('makeTodo')
            ->with('This test is incomplete.')
            ->andReturn(new TestStatement('/** @todo This test is incomplete. */'));

        $this->methodFactory->makeTestable($class, $reflectionMethod);

        $method = $class->getMethods()->first();

        $this->assertSame('testFooBar', $method->getName());
        $this->assertSame('public', $method->getVisibility());
        $this->assertSame([
            ['/** @todo This test is incomplete. */'],
            ['$this->markTestIncomplete()'],
        ], $method->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }
}
