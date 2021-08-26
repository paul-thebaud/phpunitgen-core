<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Laravel\Policy;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory;
use PhpUnitGen\Core\Contracts\Generators\Factories\ValueFactory;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Factories\StatementFactory as StatementFactoryImpl;
use PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyMethodFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestStatement;
use PhpUnitGen\Core\Reflection\ReflectionType as PugReflectionType;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflection\ReflectionMethod;
use PHPStan\BetterReflection\Reflection\ReflectionParameter;
use PHPStan\BetterReflection\Reflection\ReflectionProperty;
use PHPStan\BetterReflection\Reflection\ReflectionType;
use Tests\PhpUnitGen\Core\TestCase;
use Tightenco\Collect\Support\Collection;

/**
 * Class PolicyMethodFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyMethodFactory
 */
class PolicyMethodFactoryTest extends TestCase
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
     * @var PolicyMethodFactory
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
        $this->methodFactory = new PolicyMethodFactory();
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

        $this->assertSame([
            ['parent::setUp()'],
            [''],
            ['/** @todo Correctly instantiate tested object to use it. */'],
            ['$this->foo = new Foo()'],
            ['$this->user = new User()'],
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
            'getShortName'           => 'getBar',
            'getDeclaringClass'      => $class->getReflectionClass(),
            'getReturnType'          => null,
            'getDocBlockReturnTypes' => [],
            'isStatic'               => false,
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

        $this->assertSame('testGetBar', $method->getName());
        $this->assertSame('public', $method->getVisibility());
        $this->assertNull($method->getDocumentation());
        $this->assertSame([
            ['$expected = null'],
            ['$property = (new ReflectionClass(Foo::class))', '->getProperty(\'bar\')'],
            ['$property->setValue($this->foo, $expected)'],
            ['$this->assertSame($expected, $this->foo->getBar())'],
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
        $this->expectExceptionMessage('cannot generate tests for method bar, policy method cannot be static');

        $this->methodFactory->makeTestable($class, $reflectionMethod);
    }

    public function testMakeTestableWithNoParam(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionParamUser = Mockery::mock(ReflectionParameter::class);
        $reflectionTypeUser = Mockery::mock(ReflectionType::class);

        $class = new TestClass($reflectionClass, 'App\\FooTest');

        $reflectionClass->shouldReceive([
            'getShortName'           => 'Foo',
            'getImmediateProperties' => [],
        ]);

        $reflectionMethod->shouldReceive([
            'getShortName'      => 'bar',
            'getDeclaringClass' => $class->getReflectionClass(),
            'getReturnType'     => null,
            'isStatic'          => false,
            'getParameters'     => [$reflectionParamUser],
        ]);

        $reflectionParamUser->shouldReceive([
            'getName' => 'user',
            'getType' => $reflectionTypeUser,
        ]);

        $this->statementFactory->shouldReceive('makeTodo')
            ->twice()
            ->with('This test is incomplete.')
            ->andReturn(new TestStatement('/** @todo This test is incomplete. */'));
        $this->statementFactory->shouldReceive('makeAssert')
            ->once()
            ->with('false', '$this->user->can(\'bar\', [Foo::class])')
            ->andReturn(new TestStatement('$this->assertFalse($this->user->can(\'bar\', [Foo::class]))'));
        $this->statementFactory->shouldReceive('makeAssert')
            ->once()
            ->with('true', '$this->user->can(\'bar\', [Foo::class])')
            ->andReturn(new TestStatement('$this->assertTrue($this->user->can(\'bar\', [Foo::class]))'));

        $this->methodFactory->makeTestable($class, $reflectionMethod);

        $method1 = $class->getMethods()[0];

        $this->assertSame('testBarWhenUnauthorized', $method1->getName());
        $this->assertSame('public', $method1->getVisibility());
        $this->assertNull($method1->getDocumentation());
        $this->assertSame([
            ['/** @todo This test is incomplete. */'],
            ['$this->assertFalse($this->user->can(\'bar\', [Foo::class]))'],
        ], $method1->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());

        $method2 = $class->getMethods()[1];

        $this->assertSame('testBarWhenAuthorized', $method2->getName());
        $this->assertSame('public', $method2->getVisibility());
        $this->assertNull($method2->getDocumentation());
        $this->assertSame([
            ['/** @todo This test is incomplete. */'],
            ['$this->assertTrue($this->user->can(\'bar\', [Foo::class]))'],
        ], $method2->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }

    public function testMakeTestableWithSingleParam(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionParamUser = Mockery::mock(ReflectionParameter::class);
        $reflectionParamProduct = Mockery::mock(ReflectionParameter::class);
        $reflectionTypeUser = Mockery::mock(ReflectionType::class);
        $reflectionTypeProduct = Mockery::mock(ReflectionType::class);

        $class = new TestClass($reflectionClass, 'App\\FooTest');

        $reflectionClass->shouldReceive([
            'getShortName'           => 'Foo',
            'getImmediateProperties' => [],
        ]);

        $reflectionMethod->shouldReceive([
            'getShortName'      => 'bar',
            'getDeclaringClass' => $class->getReflectionClass(),
            'getReturnType'     => null,
            'isStatic'          => false,
            'getParameters'     => [$reflectionParamUser, $reflectionParamProduct],
        ]);

        $reflectionParamUser->shouldReceive([
            'getName' => 'user',
            'getType' => $reflectionTypeUser,
        ]);
        $reflectionParamProduct->shouldReceive([
            'getName' => 'product',
            'getType' => $reflectionTypeProduct,
        ]);

        $reflectionTypeProduct->shouldReceive([
            '__toString' => 'string',
            'allowsNull' => false,
        ]);

        $this->valueFactory->shouldReceive('make')
            ->with($class, Mockery::on(function (PugReflectionType $reflectionType) {
                return $reflectionType->getType() === 'string';
            }))
            ->andReturn('42');

        $this->statementFactory->shouldReceive('makeTodo')
            ->twice()
            ->with('This test is incomplete.')
            ->andReturn(new TestStatement('/** @todo This test is incomplete. */'));
        $this->statementFactory->shouldReceive('makeAffect')
            ->twice()
            ->with('product', '42', false)
            ->andReturn(new TestStatement('$product = 42'));
        $this->statementFactory->shouldReceive('makeAssert')
            ->once()
            ->with('false', '$this->user->can(\'bar\', $product)')
            ->andReturn(new TestStatement('$this->assertFalse($this->user->can(\'bar\', $product))'));
        $this->statementFactory->shouldReceive('makeAssert')
            ->once()
            ->with('true', '$this->user->can(\'bar\', $product)')
            ->andReturn(new TestStatement('$this->assertTrue($this->user->can(\'bar\', $product))'));

        $this->methodFactory->makeTestable($class, $reflectionMethod);

        $method1 = $class->getMethods()[0];

        $this->assertSame('testBarWhenUnauthorized', $method1->getName());
        $this->assertSame('public', $method1->getVisibility());
        $this->assertNull($method1->getDocumentation());
        $this->assertSame([
            ['/** @todo This test is incomplete. */'],
            ['$product = 42'],
            [''],
            ['$this->assertFalse($this->user->can(\'bar\', $product))'],
        ], $method1->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());

        $method2 = $class->getMethods()[1];

        $this->assertSame('testBarWhenAuthorized', $method2->getName());
        $this->assertSame('public', $method2->getVisibility());
        $this->assertNull($method2->getDocumentation());
        $this->assertSame([
            ['/** @todo This test is incomplete. */'],
            ['$product = 42'],
            [''],
            ['$this->assertTrue($this->user->can(\'bar\', $product))'],
        ], $method2->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }

    public function testMakeTestableWithMultipleParams(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionParamUser = Mockery::mock(ReflectionParameter::class);
        $reflectionParamProduct = Mockery::mock(ReflectionParameter::class);
        $reflectionParamCategory = Mockery::mock(ReflectionParameter::class);
        $reflectionTypeUser = Mockery::mock(ReflectionType::class);
        $reflectionTypeProduct = Mockery::mock(ReflectionType::class);
        $reflectionTypeCategory = Mockery::mock(ReflectionType::class);

        $class = new TestClass($reflectionClass, 'App\\FooTest');

        $reflectionClass->shouldReceive([
            'getShortName'           => 'Foo',
            'getImmediateProperties' => [],
        ]);

        $reflectionMethod->shouldReceive([
            'getShortName'      => 'bar',
            'getDeclaringClass' => $class->getReflectionClass(),
            'getReturnType'     => null,
            'isStatic'          => false,
            'getParameters'     => [$reflectionParamUser, $reflectionParamProduct, $reflectionParamCategory],
        ]);

        $reflectionParamUser->shouldReceive([
            'getName' => 'user',
            'getType' => $reflectionTypeUser,
        ]);
        $reflectionParamProduct->shouldReceive([
            'getName' => 'product',
            'getType' => $reflectionTypeProduct,
        ]);
        $reflectionParamCategory->shouldReceive([
            'getName' => 'category',
            'getType' => $reflectionTypeCategory,
        ]);

        $reflectionTypeProduct->shouldReceive([
            '__toString' => 'string',
            'allowsNull' => false,
        ]);
        $reflectionTypeCategory->shouldReceive([
            '__toString' => '\\App\\Category',
            'allowsNull' => false,
        ]);

        $this->valueFactory->shouldReceive('make')
            ->with($class, Mockery::on(function (PugReflectionType $reflectionType) {
                return $reflectionType->getType() === 'string';
            }))
            ->andReturn('42');
        $this->valueFactory->shouldReceive('make')
            ->with($class, Mockery::on(function (PugReflectionType $reflectionType) {
                return $reflectionType->getType() === 'App\\Category';
            }))
            ->andReturn('84');

        $this->statementFactory->shouldReceive('makeTodo')
            ->twice()
            ->with('This test is incomplete.')
            ->andReturn(new TestStatement('/** @todo This test is incomplete. */'));
        $this->statementFactory->shouldReceive('makeAffect')
            ->twice()
            ->with('product', '42', false)
            ->andReturn(new TestStatement('$product = 42'));
        $this->statementFactory->shouldReceive('makeAffect')
            ->twice()
            ->with('category', '84', false)
            ->andReturn(new TestStatement('$category = 84'));
        $this->statementFactory->shouldReceive('makeAssert')
            ->once()
            ->with('false', '$this->user->can(\'bar\', [$product, $category])')
            ->andReturn(new TestStatement('$this->assertFalse($this->user->can(\'bar\', [$product, $category]))'));
        $this->statementFactory->shouldReceive('makeAssert')
            ->once()
            ->with('true', '$this->user->can(\'bar\', [$product, $category])')
            ->andReturn(new TestStatement('$this->assertTrue($this->user->can(\'bar\', [$product, $category]))'));

        $this->methodFactory->makeTestable($class, $reflectionMethod);

        $method1 = $class->getMethods()[0];

        $this->assertSame('testBarWhenUnauthorized', $method1->getName());
        $this->assertSame('public', $method1->getVisibility());
        $this->assertNull($method1->getDocumentation());
        $this->assertSame([
            ['/** @todo This test is incomplete. */'],
            ['$product = 42'],
            ['$category = 84'],
            [''],
            ['$this->assertFalse($this->user->can(\'bar\', [$product, $category]))'],
        ], $method1->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());

        $method2 = $class->getMethods()[1];

        $this->assertSame('testBarWhenAuthorized', $method2->getName());
        $this->assertSame('public', $method2->getVisibility());
        $this->assertNull($method2->getDocumentation());
        $this->assertSame([
            ['/** @todo This test is incomplete. */'],
            ['$product = 42'],
            ['$category = 84'],
            [''],
            ['$this->assertTrue($this->user->can(\'bar\', [$product, $category]))'],
        ], $method2->getStatements()->map(function (TestStatement $statement) {
            return $statement->getLines()->toArray();
        })->toArray());
    }
}
