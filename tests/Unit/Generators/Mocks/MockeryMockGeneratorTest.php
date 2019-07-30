<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Parsers;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class MockeryMockGeneratorTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Mocks\MockeryMockGenerator
 */
class MockeryMockGeneratorTest extends TestCase
{
    /**
     * @var ImportFactory|Mock
     */
    protected $importFactory;

    /**
     * @var MockeryMockGenerator
     */
    protected $mockeryMockGenerator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importFactory = Mockery::mock(ImportFactory::class);
        $this->mockeryMockGenerator = new MockeryMockGenerator();
        $this->mockeryMockGenerator->setImportFactory($this->importFactory);
    }

    public function testGetMockType(): void
    {
        $class = Mockery::mock(TestClass::class);
        $import = Mockery::mock(TestImport::class);

        $this->importFactory->shouldReceive('make')
            ->once()
            ->with($class, 'Mockery\\Mock')
            ->andReturn($import);

        $this->assertSame($import, $this->mockeryMockGenerator->getMockType($class));
    }

    public function testGenerateMock(): void
    {
        $class = Mockery::mock(TestClass::class);
        $import1 = Mockery::mock(TestImport::class);
        $import2 = Mockery::mock(TestImport::class);

        $this->importFactory->shouldReceive('make')
            ->once()
            ->with($class, 'Mockery')
            ->andReturn($import1);
        $this->importFactory->shouldReceive('make')
            ->once()
            ->with($class, 'Foo')
            ->andReturn($import2);

        $import1->shouldReceive('getFinalName')
            ->once()
            ->andReturn('Mockery');
        $import2->shouldReceive('getFinalName')
            ->once()
            ->andReturn('Foo');

        $this->assertSame('Mockery::mock(Foo::class)', $this->mockeryMockGenerator->generateMock($class, 'Foo'));
    }
}
