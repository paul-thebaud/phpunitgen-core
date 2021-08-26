<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Factories;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;
use Tightenco\Collect\Support\Collection;

/**
 * Class DocumentationFactoryTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Factories\DocumentationFactory
 */
class DocumentationFactoryTest extends TestCase
{
    /**
     * @var Config|Mock
     */
    protected $config;

    /**
     * @var DocumentationFactory
     */
    protected $documentationFactory;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->config = Mockery::mock(Config::class);
        $this->documentationFactory = new DocumentationFactory();
        $this->documentationFactory->setConfig($this->config);
    }

    public function testMakeForClassWithoutCustomTags(): void
    {
        $class = Mockery::mock(TestClass::class);
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $this->config->shouldReceive('phpDoc')
            ->withNoArgs()
            ->andReturn([]);
        $this->config->shouldReceive('mergedPhpDoc')
            ->withNoArgs()
            ->andReturn([]);

        $reflectionClass->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('App\\Foo');
        $reflectionClass->shouldReceive('getDocComment')
            ->withNoArgs()
            ->andReturn('');

        $class->shouldReceive('getShortName')
            ->withNoArgs()
            ->andReturn('FooTest');
        $class->shouldReceive('getReflectionClass')
            ->withNoArgs()
            ->andReturn($reflectionClass);

        $doc = $this->documentationFactory->makeForClass($class);

        $this->assertSame([
            'Class FooTest.',
            '',
            '@covers \\App\\Foo',
        ], $doc->getLines()->toArray());
    }

    public function testMakeForClassWithCustomTags(): void
    {
        $class = Mockery::mock(TestClass::class);
        $reflectionClass = Mockery::mock(ReflectionClass::class);

        $this->config->shouldReceive('phpDoc')
            ->withNoArgs()
            ->andReturn(['@author John', '@copyright John']);
        $this->config->shouldReceive('mergedPhpDoc')
            ->withNoArgs()
            ->andReturn(['author', 'since', 'copyright']);

        $reflectionClass->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('App\\Foo');
        $reflectionClass->shouldReceive('getDocComment')
            ->withNoArgs()
            ->andReturn("/**\n * @author John\n * @since 1.0.0\n * @internal\n*/");

        $class->shouldReceive('getShortName')
            ->withNoArgs()
            ->andReturn('FooTest');
        $class->shouldReceive('getReflectionClass')
            ->withNoArgs()
            ->andReturn($reflectionClass);

        $doc = $this->documentationFactory->makeForClass($class);

        $this->assertSame([
            'Class FooTest.',
            '',
            '@author John',
            '@since 1.0.0',
            '@copyright John',
            '',
            '@covers \\App\\Foo',
        ], $doc->getLines()->toArray());
    }

    public function testMakeForPropertyWithOneType(): void
    {
        $property = Mockery::mock(TestProperty::class);

        $doc = $this->documentationFactory->makeForProperty($property, 'string');

        $this->assertSame([
            '@var string',
        ], $doc->getLines()->toArray());
    }

    public function testMakeForPropertyWithMultipleTypes(): void
    {
        $property = Mockery::mock(TestProperty::class);
        $type = Mockery::mock(TestImport::class);

        $type->shouldReceive('getFinalName')
            ->withNoArgs()
            ->andReturn('Foo');

        $doc = $this->documentationFactory->makeForProperty($property, ['string', $type]);

        $this->assertSame([
            '@var string|Foo',
        ], $doc->getLines()->toArray());

        $doc = $this->documentationFactory->makeForProperty($property, new Collection(['string', $type]));

        $this->assertSame([
            '@var string|Foo',
        ], $doc->getLines()->toArray());
    }

    public function testMakeForInheritedMethod(): void
    {
        $method = Mockery::mock(TestMethod::class);

        $doc = $this->documentationFactory->makeForInheritedMethod($method);

        $this->assertSame(['{@inheritdoc}'], $doc->getLines()->toArray());
    }
}
