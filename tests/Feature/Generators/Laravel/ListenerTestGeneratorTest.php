<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Feature\Generators\Laravel;

use PhpUnitGen\Core\Generators\Tests\Laravel\Listener\ListenerTestGenerator;
use Tests\PhpUnitGen\Core\Feature\Generators\AbstractGeneratorTester;

/**
 * Class ListenerTestGeneratorTest.
 */
class ListenerTestGeneratorTest extends AbstractGeneratorTester
{
    public function testItGeneratesTests(): void
    {
        $this->assertGeneratedIs('Laravel/Listener/Rendered', 'Laravel/Listener/Source', [
            'implementations' => ListenerTestGenerator::implementations(),
        ]);
    }
}
