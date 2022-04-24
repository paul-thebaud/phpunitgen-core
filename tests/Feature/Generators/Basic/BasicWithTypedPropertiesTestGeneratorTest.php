<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Feature\Generators\Basic;

use PhpUnitGen\Core\Generators\Tests\Basic\BasicTestGenerator;
use Tests\PhpUnitGen\Core\Feature\Generators\AbstractGeneratorTester;

/**
 * Class BasicWithTypedPropertiesTestGeneratorTest.
 */
class BasicWithTypedPropertiesTestGeneratorTest extends AbstractGeneratorTester
{
    public function testItGeneratesTests(): void
    {
        $this->assertGeneratedIs('BasicWithTypedProperties/Rendered', 'BasicWithTypedProperties/Source', [
            'implementations'          => BasicTestGenerator::implementations(),
            'testClassTypedProperties' => true,
        ]);
    }
}
