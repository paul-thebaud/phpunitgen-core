<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Mocks;

use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;

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
    public function getMockType(TestClass $class): TestImport
    {
        return $this->importFactory->create($class, 'PHPUnit\\Framework\\MockObject\\MockObject');
    }

    /**
     * {@inheritdoc}
     */
    public function generateMock(TestClass $class, string $type): string
    {
        $mockedType = $this->importFactory->create($class, $type);

        return "\$this->getMock({$mockedType->getFinalName()}::class)";
    }
}
