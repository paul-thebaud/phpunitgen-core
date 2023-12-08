<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Helpers;

use Mockery;
use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Types\Context;
use PhpUnitGen\Core\Helpers\Reflect;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Roave\BetterReflection\SourceLocator\Located\LocatedSource;
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

        self::assertSame([], Reflect::methods($reflectionClass)->toArray());
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

        self::assertSame($reflectionMethod1, Reflect::method($reflectionClass, 'foo'));
        self::assertSame($reflectionMethod2, Reflect::method($reflectionClass, 'bar'));
        self::assertNull(Reflect::method($reflectionClass, 'baz'));
    }

    public function testProperties(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $reflectionClass->shouldReceive('getImmediateProperties')
            ->andReturn([]);

        self::assertSame([], Reflect::properties($reflectionClass)->toArray());
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

        self::assertSame($reflectionProperty1, Reflect::property($reflectionClass, 'foo'));
        self::assertSame($reflectionProperty2, Reflect::property($reflectionClass, 'bar'));
        self::assertNull(Reflect::property($reflectionClass, 'baz'));
    }

    public function testParameters(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $reflectionMethod->shouldReceive('getParameters')
            ->andReturn([]);

        self::assertSame([], Reflect::parameters($reflectionMethod)->toArray());
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

        self::assertNotNull($newReflectionType);
        self::assertFalse($newReflectionType->isNullable());
        self::assertSame('string', $newReflectionType->getType());
    }

    public function testParameterTypeWithDocBlockType(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionParameter = Mockery::mock(ReflectionParameter::class);
        $reflectionSource = Mockery::mock(LocatedSource::class);

        $reflectionSource->shouldReceive(['getSource' => '']);

        $reflectionClass->shouldReceive([
            'getNamespaceName' => '',
            'getLocatedSource' => $reflectionSource,
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

        self::assertNotNull($newReflectionType);
        self::assertTrue($newReflectionType->isNullable());
        self::assertSame('string', $newReflectionType->getType());
    }

    public function testParameterTypeWithNone(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionParameter = Mockery::mock(ReflectionParameter::class);
        $reflectionSource = Mockery::mock(LocatedSource::class);

        $reflectionSource->shouldReceive(['getSource' => '']);

        $reflectionClass->shouldReceive([
            'getNamespaceName' => '',
            'getLocatedSource' => $reflectionSource,
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

        self::assertNull($newReflectionType);
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

        self::assertNotNull($newReflectionType);
        self::assertFalse($newReflectionType->isNullable());
        self::assertSame('string', $newReflectionType->getType());
    }

    public function testReturnTypeWithDocBlockType(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionSource = Mockery::mock(LocatedSource::class);

        $reflectionSource->shouldReceive(['getSource' => '']);

        $reflectionClass->shouldReceive([
            'getNamespaceName' => '',
            'getLocatedSource' => $reflectionSource,
        ]);
        $reflectionMethod->shouldReceive([
            'getReturnType'     => null,
            'getDocComment'     => "/*\n * @return string|null\n */",
            'getDeclaringClass' => $reflectionClass,
        ]);

        $newReflectionType = Reflect::returnType($reflectionMethod);

        self::assertNotNull($newReflectionType);
        self::assertTrue($newReflectionType->isNullable());
        self::assertSame('string', $newReflectionType->getType());
    }

    public function testReturnTypeWithNone(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);

        $reflectionMethod->shouldReceive([
            'getReturnType' => null,
            'getDocComment' => '',
        ]);

        $newReflectionType = Reflect::returnType($reflectionMethod);

        self::assertNull($newReflectionType);
    }

    public function testDocBlockWhenDefaultFactoryAndEmptyDocComment(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionMethod->shouldReceive('getDocComment')
            ->withNoArgs()
            ->andReturn('');

        self::assertNull(Reflect::docBlock($reflectionMethod));
    }

    public function testDocBlockWhenDefaultFactoryAndNotEmptyDocComment(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionSource = Mockery::mock(LocatedSource::class);

        $reflectionSource->shouldReceive(['getSource' => '']);

        $reflectionClass->shouldReceive([
            'getNamespaceName' => '',
            'getLocatedSource' => $reflectionSource,
        ]);
        $reflectionMethod->shouldReceive('getDocComment')
            ->withNoArgs()
            ->andReturn('/** @author John Doe */');
        $reflectionMethod->shouldReceive('getDeclaringClass')
            ->withNoArgs()
            ->andReturn($reflectionClass);

        self::assertInstanceOf(DocBlock::class, Reflect::docBlock($reflectionMethod));
    }

    public function testDocBlockWhenCustomFactory(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionSource = Mockery::mock(LocatedSource::class);

        $reflectionSource->shouldReceive(['getSource' => '']);

        $reflectionClass->shouldReceive([
            'getNamespaceName' => '',
            'getLocatedSource' => $reflectionSource,
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

        self::assertSame($docBlock, Reflect::docBlock($reflectionMethod));

        Reflect::setDocBlockFactory(null);
    }

    public function testDocBlockTagsWhenEmptyDocComment(): void
    {
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionMethod->shouldReceive('getDocComment')
            ->withNoArgs()
            ->andReturn('');

        $tags = Reflect::docBlockTags($reflectionMethod);

        self::assertTrue($tags->isEmpty());
    }

    public function testDocBlockTagsWhenNotEmptyDocComment(): void
    {
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $reflectionMethod = Mockery::mock(ReflectionMethod::class);
        $reflectionSource = Mockery::mock(LocatedSource::class);

        $reflectionSource->shouldReceive(['getSource' => '']);

        $reflectionClass->shouldReceive([
            'getNamespaceName' => '',
            'getLocatedSource' => $reflectionSource,
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

        self::assertFalse($tags->isEmpty());
        self::assertCount(2, $tags);
        self::assertSame('@author John Doe', $tags->get(0)->render());
        self::assertSame('@see https://example.com', $tags->get(1)->render());
    }
}
