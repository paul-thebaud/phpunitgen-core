<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Feature\Generators\Laravel;

use PhpUnitGen\Core\Generators\Tests\Laravel\Resource\ResourceTestGenerator;
use Tests\PhpUnitGen\Core\Feature\Generators\AbstractGeneratorTester;

/**
 * Class ResourceTestGeneratorTest.
 */
class ResourceTestGeneratorTest extends AbstractGeneratorTester
{
    public function testItGeneratesTests(): void
    {
        $this->assertGeneratedIs('Laravel/Resource/Rendered', 'Laravel/Resource/Source', [
            'implementations' => ResourceTestGenerator::implementations(),
        ]);
    }
}
