<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Feature\Generators\Laravel;

use PhpUnitGen\Core\Generators\Tests\Laravel\Rule\RuleTestGenerator;
use Tests\PhpUnitGen\Core\Feature\Generators\AbstractGeneratorTester;

/**
 * Class RuleTestGeneratorTest.
 */
class RuleTestGeneratorTest extends AbstractGeneratorTester
{
    public function testItGeneratesTests(): void
    {
        $this->assertGeneratedIs('Laravel/Rule/Rendered', 'Laravel/Rule/Source', [
            'implementations' => RuleTestGenerator::implementations(),
        ]);
    }
}
