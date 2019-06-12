<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Mocks;

use PhpUnitGen\Core\Models\TestClass;

/**
 * Class PhpUnitMockGenerator.
 *
 * The mock generator for PHPUnit.
 *
 * @package PhpUnitGen\Core
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
     * {@inheritdoc}
     */
    protected function getMockCreationLine(TestClass $testClass, string $class): string
    {
        return "\$this->getMockBuilder({$class}::class)->getMock();";
    }
}
