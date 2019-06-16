<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Mocks;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestStatement;

/**
 * Class PhpUnitMockGenerator.
 *
 * The mock generator for PHPUnit.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class PhpUnitMockGenerator extends AbstractMockGenerator
{
    /**
     * {@inheritdoc}
     */
    protected function getMockClass(): string
    {
        return 'PHPUnit\\Framework\\MockObject\\MockObject';
    }

    /**
     * Get the mock creation statement for the given test class and reflection parameter.
     *
     * @param TestClass $class
     * @param string    $type
     *
     * @return TestStatement
     */
    protected function mockCreationStatement(TestClass $class, string $type): TestStatement
    {
        return new TestStatement("\$this->getMock({$type}::class);");
    }
}
