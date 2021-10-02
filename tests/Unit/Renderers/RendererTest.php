<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Renderers;

use Mockery;
use Roave\BetterReflection\Reflection\ReflectionClass;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestParameter;
use PhpUnitGen\Core\Models\TestProperty;
use PhpUnitGen\Core\Models\TestProvider;
use PhpUnitGen\Core\Models\TestStatement;
use PhpUnitGen\Core\Models\TestTrait;
use PhpUnitGen\Core\Renderers\RenderedLine;
use PhpUnitGen\Core\Renderers\Renderer;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class RendererTest.
 *
 * @covers \PhpUnitGen\Core\Renderers\Renderer
 */
class RendererTest extends TestCase
{
    /**
     * @var Renderer
     */
    protected $renderer;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->renderer = new Renderer();
    }

    public function testItRendersWithoutLine(): void
    {
        $this->assertSame('', $this->renderer->getRendered()->toString());
    }

    public function testItRendersWithMultipleLine(): void
    {
        $this->renderer->visitTestImport(new TestImport('Foo\\Bar'));
        $this->renderer->visitTestImport(new TestImport('Foo\\Baz'));

        $this->assertSame(
            'use Foo\\Bar;
use Foo\\Baz;',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersImportWithoutAlias(): void
    {
        $this->renderer->visitTestImport(new TestImport('Foo\\Bar'));

        $this->assertCount(1, $this->renderer->getLines());
        $this->assertSame(
            'use Foo\\Bar;',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersImportWithAlias(): void
    {
        $this->renderer->visitTestImport(new TestImport('Foo\\Bar', 'BarAlias'));

        $this->assertCount(1, $this->renderer->getLines());
        $this->assertSame(
            'use Foo\\Bar as BarAlias;',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersClassWithoutAnyObjects(): void
    {
        $this->renderer->visitTestClass(new TestClass(Mockery::mock(ReflectionClass::class), 'FooTest'));

        $this->assertCount(6, $this->renderer->getLines());
        $this->assertSame(
            '<?php

class FooTest extends TestCase
{
}
',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersClassWithAllObjects(): void
    {
        $class = new TestClass(Mockery::mock(ReflectionClass::class), 'Tests\\Bar\\FooTest');

        $import1 = Mockery::mock(TestImport::class);
        $import1->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $import1->shouldReceive('getName')
            ->once()
            ->with()
            ->andReturn('Import1');
        $import2 = Mockery::mock(TestImport::class);
        $import2->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $import2->shouldReceive('getName')
            ->once()
            ->with()
            ->andReturn('Import2');
        $class->getImports()->add($import1);
        $class->getImports()->add($import2);

        $documentation = Mockery::mock(TestDocumentation::class);
        $documentation->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $class->setDocumentation($documentation);

        $trait1 = Mockery::mock(TestTrait::class);
        $trait1->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $trait1->shouldReceive('getName')
            ->once()
            ->with()
            ->andReturn('Trait1');
        $trait2 = Mockery::mock(TestTrait::class);
        $trait2->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $trait2->shouldReceive('getName')
            ->once()
            ->with()
            ->andReturn('Trait2');
        $class->getTraits()->add($trait1);
        $class->getTraits()->add($trait2);

        $property1 = Mockery::mock(TestProperty::class);
        $property1->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $property2 = Mockery::mock(TestProperty::class);
        $property2->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $class->getProperties()->add($property1);
        $class->getProperties()->add($property2);

        $method1 = Mockery::mock(TestMethod::class);
        $method1->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $method2 = Mockery::mock(TestMethod::class);
        $method2->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $class->getMethods()->add($method1);
        $class->getMethods()->add($method2);

        $this->renderer->visitTestClass($class);

        $this->assertCount(9, $this->renderer->getLines());
        $this->assertSame(
            '<?php

namespace Tests\\Bar;


class FooTest extends TestCase
{
}
',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersTrait(): void
    {
        $this->renderer->visitTestTrait(new TestTrait('Foo'));

        $this->assertCount(1, $this->renderer->getLines());
        $this->assertSame(
            'use Foo;',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersPropertyWithoutDocumentation(): void
    {
        $this->renderer->visitTestProperty(new TestProperty('fooMock'));

        $this->assertCount(2, $this->renderer->getLines());
        $this->assertSame(
            'protected $fooMock;
',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersPropertyWithDocumentation(): void
    {
        $property = new TestProperty('fooMock');

        $documentation = Mockery::mock(TestDocumentation::class);
        $documentation->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $property->setDocumentation($documentation);

        $this->renderer->visitTestProperty($property);

        $this->assertCount(2, $this->renderer->getLines());
        $this->assertSame(
            'protected $fooMock;
',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersMethodWithoutAnyObject(): void
    {
        $this->renderer->visitTestMethod(new TestMethod('testFoo'));

        $this->assertCount(4, $this->renderer->getLines());
        $this->assertSame(
            'public function testFoo(): void
{
}
',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersMethodWithAllObjects(): void
    {
        $method = new TestMethod('testFoo');

        $documentation = Mockery::mock(TestDocumentation::class);
        $documentation->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $method->setDocumentation($documentation);

        $parameter1 = Mockery::mock(TestParameter::class);
        $parameter1->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $parameter2 = Mockery::mock(TestParameter::class);
        $parameter2->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $parameter3 = Mockery::mock(TestParameter::class);
        $parameter3->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $method->getParameters()->add($parameter1);
        $method->getParameters()->add($parameter2);
        $method->getParameters()->add($parameter3);

        $statement1 = Mockery::mock(TestStatement::class);
        $statement1->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $statement2 = Mockery::mock(TestStatement::class);
        $statement2->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $method->getStatements()->add($statement1);
        $method->getStatements()->add($statement2);

        $provider = Mockery::mock(TestProvider::class);
        $provider->shouldReceive('setTestMethod')
            ->once()
            ->with($method)
            ->andReturnSelf();
        $provider->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $method->setProvider($provider);

        $this->renderer->visitTestMethod($method);

        $this->assertCount(4, $this->renderer->getLines());
        $this->assertSame(
            'public function testFoo(, , ): void
{
}
',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersParameterWithoutType(): void
    {
        $this->renderer->getLines()->add(new RenderedLine(0, ''));

        $this->renderer->visitTestParameter(new TestParameter('expected'));

        $this->assertSame(
            '$expected',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersParameterWithType(): void
    {
        $this->renderer->getLines()->add(new RenderedLine(0, ''));

        $this->renderer->visitTestParameter(new TestParameter('expected', 'int'));

        $this->assertSame(
            'int $expected',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersProviderWithoutData(): void
    {
        $this->renderer->visitTestProvider(new TestProvider('sumDataProvider', []));

        $this->assertCount(6, $this->renderer->getLines());
        $this->assertSame(
            'public function sumDataProvider(): array
{
    return [
    ];
}
',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersProviderWithDataAndDocumentation(): void
    {
        $provider = new TestProvider('sumDataProvider', [
            ['0', '0', '0'],
            ['0', '5', '5'],
            ['5', '0', '5'],
            ['5', '5', '10'],
        ]);

        $documentation = Mockery::mock(TestDocumentation::class);
        $documentation->shouldReceive('accept')
            ->once()
            ->with($this->renderer);
        $provider->setDocumentation($documentation);

        $this->renderer->visitTestProvider($provider);

        $this->assertCount(10, $this->renderer->getLines());
        $this->assertSame(
            'public function sumDataProvider(): array
{
    return [
        [0, 0, 0],
        [0, 5, 5],
        [5, 0, 5],
        [5, 5, 10],
    ];
}
',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersStatementWithoutLine(): void
    {
        $this->renderer->visitTestStatement(new TestStatement());

        $this->assertCount(0, $this->renderer->getLines());
        $this->assertSame(
            '',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersStatementWithSingleLine(): void
    {
        $this->renderer->visitTestStatement(new TestStatement('$this->assertTrue(true)'));

        $this->assertCount(1, $this->renderer->getLines());
        $this->assertSame(
            '$this->assertTrue(true);',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersStatementWithMultipleLines(): void
    {
        $statement = new TestStatement('$this->getMockBuilder(Foo::class)');
        $statement->addLine('// A blank line to test comma are not added.');
        $statement->addLine('');
        $statement->addLine('->setConstructorArgs([$this->barMock])');
        $statement->addLine('->getMock()');

        $this->renderer->visitTestStatement($statement);

        $this->assertCount(5, $this->renderer->getLines());
        $this->assertSame(
            '$this->getMockBuilder(Foo::class)
    // A blank line to test comma are not added.

    ->setConstructorArgs([$this->barMock])
    ->getMock();',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersStatementWithDocComment(): void
    {
        $this->renderer->visitTestStatement(new TestStatement('/** @todo */'));

        $this->assertCount(1, $this->renderer->getLines());
        $this->assertSame(
            '/** @todo */',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersDocumentationWithoutLine(): void
    {
        $this->renderer->visitTestDocumentation(new TestDocumentation());

        $this->assertCount(0, $this->renderer->getLines());
        $this->assertSame(
            '',
            $this->renderer->getRendered()->toString()
        );
    }

    public function testItRendersDocumentationWithLines(): void
    {
        $documentation = new TestDocumentation('@covers Foo');
        $documentation->addLine();
        $documentation->addLine('@author John Doe');

        $this->renderer->visitTestDocumentation($documentation);

        $this->assertCount(5, $this->renderer->getLines());
        $this->assertSame(
            '/**
 * @covers Foo
 *
 * @author John Doe
 */',
            $this->renderer->getRendered()->toString()
        );
    }
}
