<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Factories;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\Factories\TypeFactory;
use PhpUnitGen\Core\Generators\Factories\DocumentationFactory;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestProperty;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\SourceLocator\Located\LocatedSource;
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
     * @var TypeFactory|Mock
     */
    protected $typeFactory;

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
        $this->typeFactory = Mockery::mock(TypeFactory::class);
        $this->documentationFactory = new DocumentationFactory();
        $this->documentationFactory->setConfig($this->config);
        $this->documentationFactory->setTypeFactory($this->typeFactory);
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
        $reflectionSource = Mockery::mock(LocatedSource::class);

        $this->config->shouldReceive('phpDoc')
            ->withNoArgs()
            ->andReturn(['@author John', '@copyright John']);
        $this->config->shouldReceive('mergedPhpDoc')
            ->withNoArgs()
            ->andReturn(['author', 'since', 'copyright']);

        $reflectionSource->shouldReceive(['getSource' => '']);

        $reflectionClass->shouldReceive('getName')
            ->withNoArgs()
            ->andReturn('App\\Foo');
        $reflectionClass->shouldReceive('getNamespaceName')
            ->withNoArgs()
            ->andReturn('');
        $reflectionClass->shouldReceive('getLocatedSource')
            ->withNoArgs()
            ->andReturn($reflectionSource);
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

    public function testMakeForProperty(): void
    {
        $property = Mockery::mock(TestProperty::class);

        $types = new Collection(['string', 'bool']);
        $this->typeFactory->shouldReceive('formatTypes')
            ->once()
            ->with($types)
            ->andReturn('string|bool');

        $doc = $this->documentationFactory->makeForProperty($property, $types);

        $this->assertSame([
            '@var string|bool',
        ], $doc->getLines()->toArray());
    }

    public function testMakeForInheritedMethod(): void
    {
        $method = Mockery::mock(TestMethod::class);

        $doc = $this->documentationFactory->makeForInheritedMethod($method);

        $this->assertSame(['{@inheritdoc}'], $doc->getLines()->toArray());
    }
}
