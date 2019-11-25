<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Feature\Generators\Laravel;

use PhpUnitGen\Core\Generators\Tests\Laravel\Controller\ControllerTestGenerator;
use Tests\PhpUnitGen\Core\Feature\Generators\AbstractGeneratorTester;

/**
 * Class ControllerTestGeneratorTest.
 */
class ControllerTestGeneratorTest extends AbstractGeneratorTester
{
    public function testItGeneratesTests(): void
    {
        $this->assertGeneratedIs('Laravel/Controller/Rendered', 'Laravel/Controller/Source', [
            'implementations' => ControllerTestGenerator::implementations(),
        ]);
    }
}
