<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Helpers;

use Mockery;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Types\Context;
use PhpParser\Node\Stmt\Namespace_;
use PhpUnitGen\Core\Helpers\Reflect;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Tests\PhpUnitGen\Core\Helpers\PhpVersionDependents;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class ReflectTest.
 *
 * @covers \PhpUnitGen\Core\Helpers\Reflect
 */
class ReflectTest extends TestCase
{
    public function testMethods(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $reflectionClass->shouldReceive('getImmediateMethods')
            ->andReturn([]);

        $this->assertSame([], Reflect::methods($reflectionClass)->toArray());
    }

    public function testMethod(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $reflectionMethod1 = Mockery::mock(ReflectionMethod::class);
        $reflectionMethod1->shouldReceive('getShortName')
            ->andReturn('foo');
        $reflectionMethod2 = Mockery::mock(ReflectionMethod::class);
        $reflectionMethod2->shouldReceive('getShortName')
            ->andReturn('bar');

        $reflectionClass->shouldReceive('getImmediateMethods')
            ->andReturn([
                $reflectionMethod1,
                $reflectionMethod2,
            ]);

        $this->assertSame($reflectionMethod1, Reflect::method($reflectionClass, 'foo'));
        $this->assertSame($reflectionMethod2, Reflect::method($reflectionClass, 'bar'));
        $this->assertNull(Reflect::method($reflectionClass, 'baz'));
    }

    public function testProperties(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $reflectionClass->shouldReceive('getImmediateProperties')
            ->andReturn([]);

        $this->assertSame([], Reflect::properties($reflectionClass)->toArray());
    }

    public function testProperty(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $reflectionProperty1 = Mockery::mock(ReflectionProperty::class);
        $reflectionProperty1->shouldReceive('getName')
            ->andReturn('foo');
        $reflectionProperty2 = Mockery::mock(ReflectionProperty::class);
        $reflectionProperty2->shouldReceive('getName')
            ->andReturn('bar');

        $reflectionClass->shouldReceive('getImmediateProperties')
            ->andReturn([
                $reflectionProperty1,
                $reflectionProperty2,
            ]);

        $this->assertSame($reflectionProperty1, Reflect::property($reflectionClass, 'foo'));
        $this->assertSame($reflectionProperty2, Reflect::property($reflectionClass, 'bar'));
        $this->assertNull(Reflect::property($reflectionClass, 'baz'));
    }

    public function testParameters(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $reflectionMethod->shouldReceive('getParameters')
            ->andReturn([]);

        $this->assertSame([], Reflect::parameters($reflectionMethod)->toArray());
    }

    public function testParameterTypeWithReflectionType(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionParameter = Mockery::mock(ReflectionParameter::class);
        $reflectionType = PhpVersionDependents::makeReflectionTypeMock();

        $reflectionMethod->shouldReceive([
            'getDocComment' => '',
        ]);
        $reflectionParameter->shouldReceive([
            'getType'              => $reflectionType,
            'getDeclaringFunction' => $reflectionMethod,
        ]);
        $reflectionType->shouldReceive([
            '__toString' => 'string',
            'allowsNull' => false,
        ]);

        $newReflectionType = Reflect::parameterType($reflectionParameter);

        $this->assertNotNull($newReflectionType);
        $this->assertFalse($newReflectionType->isNullable());
        $this->assertSame('string', $newReflectionType->getType());
    }

    public function testParameterTypeWithDocBlockType(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionParameter = Mockery::mock(ReflectionParameter::class);

        $reflectionClass->shouldReceive([
            'getDeclaringNamespaceAst' => new Namespace_(),
        ]);
        $reflectionMethod->shouldReceive([
            'getDocComment'     => "/*\n * @param string|null \$foo\n */",
            'getDeclaringClass' => $reflectionClass,
        ]);
        $reflectionParameter->shouldReceive([
            'getName'              => 'foo',
            'getType'              => null,
            'getDeclaringFunction' => $reflectionMethod,
        ]);

        $newReflectionType = Reflect::parameterType($reflectionParameter);

        $this->assertNotNull($newReflectionType);
        $this->assertTrue($newReflectionType->isNullable());
        $this->assertSame('string', $newReflectionType->getType());
    }

    public function testParameterTypeWithNone(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionParameter = Mockery::mock(ReflectionParameter::class);

        $reflectionClass->shouldReceive([
            'getDeclaringNamespaceAst' => new Namespace_(),
        ]);
        $reflectionMethod->shouldReceive([
            'getDocComment'     => '',
            'getDeclaringClass' => $reflectionClass,
        ]);
        $reflectionParameter->shouldReceive([
            'getType'              => null,
            'getDeclaringFunction' => $reflectionMethod,
        ]);

        $newReflectionType = Reflect::parameterType($reflectionParameter);

        $this->assertNull($newReflectionType);
    }

    public function testReturnTypeWithReflectionType(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionType = PhpVersionDependents::makeReflectionTypeMock();

        $reflectionMethod->shouldReceive([
            'getReturnType' => $reflectionType,
            'getDocComment' => '',
        ]);
        $reflectionType->shouldReceive([
            '__toString' => 'string',
            'allowsNull' => false,
        ]);

        $newReflectionType = Reflect::returnType($reflectionMethod);

        $this->assertNotNull($newReflectionType);
        $this->assertFalse($newReflectionType->isNullable());
        $this->assertSame('string', $newReflectionType->getType());
    }

    public function testReturnTypeWithDocBlockType(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $reflectionClass->shouldReceive([
            'getDeclaringNamespaceAst' => new Namespace_(),
        ]);
        $reflectionMethod->shouldReceive([
            'getReturnType'     => null,
            'getDocComment'     => "/*\n * @return string|null\n */",
            'getDeclaringClass' => $reflectionClass,
        ]);

        $newReflectionType = Reflect::returnType($reflectionMethod);

        $this->assertNotNull($newReflectionType);
        $this->assertTrue($newReflectionType->isNullable());
        $this->assertSame('string', $newReflectionType->getType());
    }

    public function testReturnTypeWithNone(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $reflectionMethod->shouldReceive([
            'getReturnType' => null,
            'getDocComment' => '',
        ]);

        $newReflectionType = Reflect::returnType($reflectionMethod);

        $this->assertNull($newReflectionType);
    }

    public function testDocBlockWhenDefaultFactoryAndEmptyDocComment(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionMethod->shouldReceive('getDocComment')
            ->withNoArgs()
            ->andReturn('');

        $this->assertNull(Reflect::docBlock($reflectionMethod));
    }

    public function testDocBlockWhenDefaultFactoryAndNotEmptyDocComment(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $reflectionClass->shouldReceive([
            'getDeclaringNamespaceAst' => new Namespace_(),
        ]);
        $reflectionMethod->shouldReceive('getDocComment')
            ->withNoArgs()
            ->andReturn('/** @author John Doe */');
        $reflectionMethod->shouldReceive('getDeclaringClass')
            ->withNoArgs()
            ->andReturn($reflectionClass);

        $this->assertInstanceOf(DocBlock::class, Reflect::docBlock($reflectionMethod));
    }

    public function testDocBlockWhenCustomFactory(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $reflectionClass->shouldReceive([
            'getDeclaringNamespaceAst' => new Namespace_(),
        ]);
        $reflectionMethod->shouldReceive('getDocComment')
            ->withNoArgs()
            ->andReturn('/** @author John Doe */');
        $reflectionMethod->shouldReceive('getDeclaringClass')
            ->withNoArgs()
            ->andReturn($reflectionClass);

        $docBlock = new DocBlock();

        $docBlockFactory = Mockery::mock(DocBlockFactoryInterface::class);
        $docBlockFactory->shouldReceive('create')
            ->with('/** @author John Doe */', Mockery::on(function ($arg) {
                return $arg instanceof Context;
            }))
            ->andReturn($docBlock);

        Reflect::setDocBlockFactory($docBlockFactory);

        $this->assertSame($docBlock, Reflect::docBlock($reflectionMethod));

        Reflect::setDocBlockFactory(null);
    }

    public function testDocBlockTagsWhenEmptyDocComment(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionMethod->shouldReceive('getDocComment')
            ->withNoArgs()
            ->andReturn('');

        $tags = Reflect::docBlockTags($reflectionMethod);

        $this->assertTrue($tags->isEmpty());
    }

    public function testDocBlockTagsWhenNotEmptyDocComment(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $reflectionClass->shouldReceive([
            'getDeclaringNamespaceAst' => new Namespace_(),
        ]);
        $reflectionMethod->shouldReceive('getDocComment')
            ->withNoArgs()
            ->andReturn('/**
            * @author John Doe
            * @see https://example.com
            */');
        $reflectionMethod->shouldReceive('getDeclaringClass')
            ->withNoArgs()
            ->andReturn($reflectionClass);

        $tags = Reflect::docBlockTags($reflectionMethod);

        $this->assertFalse($tags->isEmpty());
        $this->assertCount(2, $tags);
        $this->assertSame('@author John Doe', $tags->get(0)->render());
        $this->assertSame('@see https://example.com', $tags->get(1)->render());
    }
}
