<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Mocks;

use PhpUnitGen\Core\Aware\ImportFactoryAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\ImportFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;

/**
 * Class MockeryMockGenerator.
 *
 * The mock generator for Mockery.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class MockeryMockGenerator implements MockGenerator, ImportFactoryAware
{
    use ImportFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getMockType(TestClass $class): TestImport
    {
        return $this->importFactory->make($class, 'Mockery\\Mock');
    }

    /**
     * {@inheritdoc}
     */
    public function generateMock(TestClass $class, string $type): string
    {
        // Mockery must be imported to mock classes.
        $mockeryType = $this->importFactory->make($class, 'Mockery');
        $mockedType = $this->importFactory->make($class, $type);

        return "{$mockeryType->getFinalName()}::mock({$mockedType->getFinalName()}::class)";
    }
}
