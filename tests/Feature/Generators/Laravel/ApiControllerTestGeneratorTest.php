<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Feature\Generators\Laravel;

use PhpUnitGen\Core\Generators\Tests\Laravel\Controller\ControllerTestGenerator;
use Tests\PhpUnitGen\Core\Feature\Generators\AbstractGeneratorTester;

/**
 * Class ApiControllerTestGeneratorTest.
 */
class ApiControllerTestGeneratorTest extends AbstractGeneratorTester
{
    public function testItGeneratesTests(): void
    {
        $this->assertGeneratedIs('Laravel/ApiController/Rendered', 'Laravel/ApiController/Source', [
            'implementations' => ControllerTestGenerator::implementations(),
        ]);
    }
}
