<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Mocks;

use PhpUnitGen\Core\Models\TestClass;

/**
 * Class MockeryMockGenerator.
 *
 * The mock generator for Mockery.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class MockeryMockGenerator extends AbstractMockGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function getMockClass(): string
    {
        return 'Mockery\\Mock';
    }

    /**
     * {@inheritdoc}
     */
    protected function getMockCreationLine(TestClass $testClass, string $class): string
    {
        $mockeryImport = $this->createTestImport($testClass, 'Mockery');

        return "{$mockeryImport}::mock({$class}::class);";
    }
}
