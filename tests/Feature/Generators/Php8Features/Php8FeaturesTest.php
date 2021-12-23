<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Feature\Generators\Php8Features;

use PhpUnitGen\Core\Generators\Tests\Basic\BasicTestGenerator;
use Tests\PhpUnitGen\Core\Feature\Generators\AbstractGeneratorTester;

/**
 * Class Php8FeaturesTest.
 */
class Php8FeaturesTest extends AbstractGeneratorTester
{
    public function testItGeneratesTests(): void
    {
        $this->assertGeneratedIs('Php8Features/Rendered', 'Php8Features/Source', [
            'implementations' => BasicTestGenerator::implementations(),
        ]);
    }
}
