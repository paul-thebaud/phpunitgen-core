<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Feature\Generators\Basic;

use PhpUnitGen\Core\Generators\Tests\Basic\BasicTestGenerator;
use Tests\PhpUnitGen\Core\Feature\Generators\AbstractGeneratorTester;

/**
 * Class BasicTestGeneratorTest.
 */
class BasicTestGeneratorTest extends AbstractGeneratorTester
{
    public function testItGeneratesTests(): void
    {
        $this->assertGeneratedIs('Basic/Rendered', 'Basic/Source', [
            'implementations' => BasicTestGenerator::implementations(),
        ]);
    }
}
