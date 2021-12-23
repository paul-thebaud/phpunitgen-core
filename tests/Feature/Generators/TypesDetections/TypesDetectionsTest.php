<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Feature\Generators\TypesDetections;

use PhpUnitGen\Core\Generators\Tests\Basic\BasicTestGenerator;
use Tests\PhpUnitGen\Core\Feature\Generators\AbstractGeneratorTester;

/**
 * Class TypesDetectionsTest.
 */
class TypesDetectionsTest extends AbstractGeneratorTester
{
    public function testItGeneratesTests(): void
    {
        $this->assertGeneratedIs('TypesDetections/Rendered', 'TypesDetections/Source', [
            'implementations' => BasicTestGenerator::implementations(),
        ]);
    }
}
