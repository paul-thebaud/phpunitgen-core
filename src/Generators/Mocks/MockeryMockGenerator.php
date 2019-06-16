<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Mocks;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestStatement;

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
    protected function mockCreationStatement(TestClass $class, string $type): TestStatement
    {
        // Mockery must be imported to mock classes.
        $mockeryType = $this->importFactory->create($class, 'Mockery');

        return new TestStatement("{$mockeryType->getFinalName()}::mock({$type}::class);");
    }
}
