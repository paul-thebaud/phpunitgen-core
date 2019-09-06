<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Feature\Generators\Basic;

use PhpUnitGen\Core\Generators\Tests\Laravel\Policy\PolicyTestGenerator;
use Tests\PhpUnitGen\Core\Feature\Generators\AbstractGeneratorTester;

/**
 * Class PolicyTestGeneratorTest.
 */
class PolicyTestGeneratorTest extends AbstractGeneratorTester
{
    public function testItGeneratesTests(): void
    {
        $this->assertGeneratedIs('Laravel/Policy/Rendered', 'Laravel/Policy/Source', [
            'implementations' => PolicyTestGenerator::implementations(),
        ]);
    }
}
