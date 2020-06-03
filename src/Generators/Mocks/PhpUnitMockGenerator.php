<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Mocks;

use PhpUnitGen\Core\Aware\ImportFactoryAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\ImportFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\MockGenerator;
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
class PhpUnitMockGenerator implements MockGenerator, ImportFactoryAware
{
    use ImportFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getMockType(TestClass $class): TestImport
    {
        return $this->importFactory->make($class, 'PHPUnit\\Framework\\MockObject\\MockObject');
    }

    /**
     * {@inheritdoc}
     */
    public function generateMock(TestClass $class, string $type): string
    {
        $mockedType = $this->importFactory->make($class, $type);

        return "\$this->createMock({$mockedType->getFinalName()}::class)";
    }
}
