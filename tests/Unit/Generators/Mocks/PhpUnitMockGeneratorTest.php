<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Mocks;

use Mockery;
use Mockery\Mock;
use PhpUnitGen\Core\Contracts\Generators\Factories\ImportFactory;
use PhpUnitGen\Core\Generators\Mocks\PhpUnitMockGenerator;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class PhpUnitMockGeneratorTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Mocks\PhpUnitMockGenerator
 */
class PhpUnitMockGeneratorTest extends TestCase
{
    /**
     * @var ImportFactory|Mock
     */
    protected $importFactory;

    /**
     * @var PhpUnitMockGenerator
     */
    protected $phpunitMockGenerator;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importFactory = Mockery::mock(ImportFactory::class);
        $this->phpunitMockGenerator = new PhpUnitMockGenerator();
        $this->phpunitMockGenerator->setImportFactory($this->importFactory);
    }

    public function testGetMockType(): void
    {
        $class = Mockery::mock(TestClass::class);
        $import = Mockery::mock(TestImport::class);

        $this->importFactory->shouldReceive('make')
            ->once()
            ->with($class, 'PHPUnit\\Framework\\MockObject\\MockObject')
            ->andReturn($import);

        $this->assertSame($import, $this->phpunitMockGenerator->getMockType($class));
    }

    public function testGenerateMock(): void
    {
        $class = Mockery::mock(TestClass::class);
        $import = Mockery::mock(TestImport::class);

        $this->importFactory->shouldReceive('make')
            ->once()
            ->with($class, 'Foo')
            ->andReturn($import);

        $import->shouldReceive('getFinalName')
            ->once()
            ->andReturn('Foo');

        $this->assertSame('$this->createMock(Foo::class)', $this->phpunitMockGenerator->generateMock($class, 'Foo'));
    }
}
