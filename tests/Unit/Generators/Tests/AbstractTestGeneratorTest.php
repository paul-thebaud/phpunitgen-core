<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests;

use Mockery;
use Mockery\Mock;
use PHPUnit\Framework\MockObject\MockObject;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Tests\AbstractTestGenerator;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class AbstractTestGeneratorTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\AbstractTestGenerator
 */
class AbstractTestGeneratorTest extends TestCase
{
    /**
     * @var Config|Mock
     */
    protected $config;

    /**
     * @var ReflectionClass|Mock
     */
    protected $reflectionClass;

    /**
     * @var AbstractTestGenerator|MockObject
     */
    protected $abstractTestGenerator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(Config::class);
        $this->reflectionClass = Mockery::mock(ReflectionClass::class);

        $this->abstractTestGenerator = $this->getMockBuilder(AbstractTestGenerator::class)
            ->setConstructorArgs([$this->config])
            ->setMethods([
                'addSetUpTestMethod',
                'isTestable',
                'handleTestableMethod',
            ])
            ->getMockForAbstractClass();
    }

    public function testCanGenerateForWithInterface(): void
    {
        $this->reflectionClass->shouldReceive('isInterface')->andReturnTrue();

        $this->assertFalse($this->abstractTestGenerator->canGenerateFor($this->reflectionClass));
    }

    public function testCanGenerateForWithAnonymousClass(): void
    {
        $this->reflectionClass->shouldReceive('isInterface')->andReturnFalse();
        $this->reflectionClass->shouldReceive('isAnonymous')->andReturnTrue();

        $this->assertFalse($this->abstractTestGenerator->canGenerateFor($this->reflectionClass));
    }

    public function testCanGenerateForWithNormalClass(): void
    {
        $this->reflectionClass->shouldReceive('isInterface')->andReturnFalse();
        $this->reflectionClass->shouldReceive('isAnonymous')->andReturnFalse();

        $this->assertTrue($this->abstractTestGenerator->canGenerateFor($this->reflectionClass));
    }

    public function testGenerateWhenCannotGenerate(): void
    {
        $this->reflectionClass->shouldReceive('isInterface')->andReturnTrue();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('cannot generate tests for given reflection class');

        $this->abstractTestGenerator->generate($this->reflectionClass);
    }

    public function testGenerateWhenNoNamespaceNoDocNoAutomaticAndNoMethod(): void
    {
        $this->reflectionClass->shouldReceive('isInterface')->andReturnFalse();
        $this->reflectionClass->shouldReceive('isAnonymous')->andReturnFalse();
        $this->reflectionClass->shouldReceive('getName')->andReturn('Foo');
        $this->reflectionClass->shouldReceive('getDocComment')->andReturn('');
        $this->reflectionClass->shouldReceive('getImmediateMethods')->andReturn([]);

        $this->config->shouldReceive('automaticTests')->andReturnFalse();
        $this->config->shouldReceive('baseNamespace')->andReturn('');
        $this->config->shouldReceive('baseTestNamespace')->andReturn('');
        $this->config->shouldReceive('phpDoc')->andReturn([]);
        $this->config->shouldReceive('mergedPhpDoc')->andReturn([]);
        $this->config->shouldReceive('testCase')->andReturn('TestCase');

        $testClass = $this->abstractTestGenerator->generate($this->reflectionClass);

        $this->assertSame('FooTest', $testClass->getName());
        $this->assertSame($this->reflectionClass, $testClass->getReflectionClass());

        $this->assertSame([
            'Class FooTest.',
            '',
            '@covers \\Foo',
        ], $testClass->getDocumentation()->getLines()->toArray());

        $this->assertCount(2, $testClass->getImports());
        $this->assertSame('TestCase', $testClass->getImports()->get(0)->getName());
        $this->assertNull($testClass->getImports()->get(0)->getAlias());
        $this->assertSame('Foo', $testClass->getImports()->get(1)->getName());
        $this->assertNull($testClass->getImports()->get(1)->getAlias());

        $this->assertCount(0, $testClass->getTraits());

        $this->assertCount(0, $testClass->getProperties());

        $this->assertCount(0, $testClass->getMethods());
    }

    public function testGenerateWhenNamespaceDocAutomaticAndMethod(): void
    {
        $method1 = Mockery::mock(ReflectionMethod::class);
        $method1->shouldReceive('getShortName')->andReturn('foo');
        $method1->shouldReceive('isPublic')->andReturnTrue();
        $method2 = Mockery::mock(ReflectionMethod::class);
        $method2->shouldReceive('getShortName')->andReturn('bar');
        $method2->shouldReceive('isPublic')->andReturnTrue();
        $method3 = Mockery::mock(ReflectionMethod::class);
        $method3->shouldReceive('getShortName')->andReturn('baz');
        $method3->shouldReceive('isPublic')->andReturnFalse();
        $method4 = Mockery::mock(ReflectionMethod::class);
        $method4->shouldReceive('getShortName')->andReturn('methodIsExcluded');
        $method4->shouldReceive('isPublic')->andReturnTrue();

        $this->reflectionClass->shouldReceive('isInterface')->andReturnFalse();
        $this->reflectionClass->shouldReceive('isAnonymous')->andReturnFalse();
        $this->reflectionClass->shouldReceive('getName')->andReturn('App\\Services\\App\\Foo');
        $this->reflectionClass->shouldReceive('getDocComment')->andReturn('/**
        * @author John Doe
        * @author Jane Doe
        * @version 1.2.10
        * @see https://example.com/foo-doc-1
        * @see https://example.com/foo-doc-2
        * @internal
        *
        */');
        $this->reflectionClass->shouldReceive('getImmediateMethods')->andReturn([
            $method1,
            $method2,
            $method3,
            $method4,
        ]);

        $this->config->shouldReceive('automaticTests')->andReturnTrue();
        $this->config->shouldReceive('baseNamespace')->andReturn('App\\');
        $this->config->shouldReceive('baseTestNamespace')->andReturn('Tests\\');
        $this->config->shouldReceive('excludedMethods')->andReturn([
            '.*isexcl.*',
        ]);
        $this->config->shouldReceive('phpDoc')->andReturn([
            '@author John Doe',
            '@see https://example.com/test-doc1',
            '@see https://example.com/test-doc2',
        ]);
        $this->config->shouldReceive('mergedPhpDoc')->andReturn([
            'author',
            'version',
        ]);
        $this->config->shouldReceive('testCase')->andReturn('PHPUnit\\Framework\\TestCase');

        $this->abstractTestGenerator->expects($this->once())
            ->method('addSetUpTestMethod')
            ->with($this->isInstanceOf(TestClass::class))
            ->willReturnCallback(function (TestClass $testClass) {
                $testProperty = new TestProperty('user');
                $testClass->addProperty($testProperty);
            });
        $this->abstractTestGenerator->expects($this->exactly(2))
            ->method('isTestable')
            ->withConsecutive([$method1], [$method2])
            ->willReturnOnConsecutiveCalls(true, false);
        $this->abstractTestGenerator->expects($this->once())
            ->method('handleTestableMethod')
            ->with($this->isInstanceOf(TestClass::class), $method1);

        $testClass = $this->abstractTestGenerator->generate($this->reflectionClass);

        $this->assertSame('Tests\\Services\\App\\FooTest', $testClass->getName());
        $this->assertSame($this->reflectionClass, $testClass->getReflectionClass());
        $this->assertSame([
            'Class FooTest.',
            '',
            '@author John Doe',
            '@author Jane Doe',
            '@version 1.2.10',
            '@see https://example.com/test-doc1',
            '@see https://example.com/test-doc2',
            '',
            '@covers \\App\\Services\\App\\Foo',
        ], $testClass->getDocumentation()->getLines()->toArray());

        $this->assertCount(2, $testClass->getImports());
        $this->assertSame('PHPUnit\\Framework\\TestCase', $testClass->getImports()->get(0)->getName());
        $this->assertNull($testClass->getImports()->get(0)->getAlias());
        $this->assertSame('App\\Services\\App\\Foo', $testClass->getImports()->get(1)->getName());
        $this->assertNull($testClass->getImports()->get(1)->getAlias());

        $this->assertCount(0, $testClass->getTraits());

        $this->assertCount(1, $testClass->getProperties());

        $this->assertCount(2, $testClass->getMethods());

        /** @var TestMethod $tearDownMethod */
        $tearDownMethod = $testClass->getMethods()->get(0);
        $this->assertSame('tearDown', $tearDownMethod->getName());
        $this->assertSame('protected', $tearDownMethod->getVisibility());
        $this->assertSame([
            '{@inheritdoc}',
        ], $tearDownMethod->getDocumentation()->getLines()->toArray());
        $this->assertCount(0, $tearDownMethod->getParameters());
        $this->assertCount(3, $tearDownMethod->getStatements());
        $this->assertSame([
            'parent::tearDown();',
        ], $tearDownMethod->getStatements()->get(0)->getLines()->toArray());
        $this->assertSame([
            '',
        ], $tearDownMethod->getStatements()->get(1)->getLines()->toArray());
        $this->assertSame([
            'unset($this->user);',
        ], $tearDownMethod->getStatements()->get(2)->getLines()->toArray());

        /** @var TestMethod $tearDownMethod */
        $fooTestMethod = $testClass->getMethods()->get(1);
        $this->assertSame('testBar', $fooTestMethod->getName());
        $this->assertSame('public', $fooTestMethod->getVisibility());
        $this->assertNull($fooTestMethod->getDocumentation());
        $this->assertCount(0, $fooTestMethod->getParameters());
        $this->assertCount(2, $fooTestMethod->getStatements());
        $this->assertSame([
            '/** @todo This test is incomplete. */',
        ], $fooTestMethod->getStatements()->get(0)->getLines()->toArray());
        $this->assertSame([
            '$this->markTestIncomplete();',
        ], $fooTestMethod->getStatements()->get(1)->getLines()->toArray());
    }
}
