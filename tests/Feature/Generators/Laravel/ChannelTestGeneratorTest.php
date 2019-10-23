<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Feature\Generators\Laravel;

use PhpUnitGen\Core\Generators\Tests\Laravel\Channel\ChannelTestGenerator;
use Tests\PhpUnitGen\Core\Feature\Generators\AbstractGeneratorTester;

/**
 * Class ChannelTestGeneratorTest.
 */
class ChannelTestGeneratorTest extends AbstractGeneratorTester
{
    public function testItGeneratesTests(): void
    {
        $this->assertGeneratedIs('Laravel/Channel/Rendered', 'Laravel/Channel/Source', [
            'implementations' => ChannelTestGenerator::implementations(),
        ]);
    }
}
