<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Models;

use Mockery;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestProvider;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class TestProviderTest.
 *
 * @covers \PhpUnitGen\Core\Models\TestProvider
 */
class TestProviderTest extends TestCase
{
    /**
     * @var TestProvider
     */
    protected $provider;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->provider = new TestProvider('fooProvider', [['expected', 'actual']]);
    }

    public function testItConstructs(): void
    {
        self::assertSame('fooProvider', $this->provider->getName());
        self::assertSame([['expected', 'actual']], $this->provider->getData());
    }

    public function testItAcceptsRenderer(): void
    {
        $renderer = Mockery::mock(Renderer::class);

        $renderer->shouldReceive('visitTestProvider')
            ->once()
            ->with($this->provider);

        $this->provider->accept($renderer);
    }

    public function testItHasDocumentation(): void
    {
        $documentation = new TestDocumentation();

        $this->provider->setDocumentation($documentation);

        self::assertSame($documentation, $this->provider->getDocumentation());
    }
}
