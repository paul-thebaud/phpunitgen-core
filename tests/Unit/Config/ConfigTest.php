<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Parsers;

use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class ConfigTest.
 *
 * @covers \PhpUnitGen\Core\Config\Config
 */
class ConfigTest extends TestCase
{
    public function testWhenDefaultConfiguration(): void
    {
        $this->assertSame([
            'automaticTests'    => true,
            'mockWith'          => 'mockery',
            'generateWith'      => 'basic',
            'baseNamespace'     => '',
            'baseTestNamespace' => 'Tests\\',
            'excludedMethods'   => [
                '__construct',
                '__destruct',
            ],
            'mergedPhpDoc'      => [
                'author',
                'copyright',
                'license',
                'version',
            ],
            'phpDoc'            => [],
            'options'           => [],
        ], Config::make()->toArray());
    }

    public function testWhenCompleteConfiguration(): void
    {
        $this->assertSame([
            'automaticTests'    => false,
            'mockWith'          => 'phpunit',
            'generateWith'      => 'custom',
            'baseNamespace'     => 'App\\',
            'baseTestNamespace' => 'App\\Tests\\',
            'excludedMethods'   => [],
            'mergedPhpDoc'      => [],
            'phpDoc'            => ['@author John Doe'],
            'options'           => ['custom' => 'option'],
        ], Config::make([
            'automaticTests'    => false,
            'mockWith'          => 'phpunit',
            'generateWith'      => 'custom',
            'baseNamespace'     => 'App\\',
            'baseTestNamespace' => 'App\\Tests\\',
            'excludedMethods'   => [],
            'mergedPhpDoc'      => [],
            'phpDoc'            => ['@author John Doe'],
            'options'           => ['custom' => 'option'],
        ])->toArray());
    }

    public function testMissingNullOrInvalidPropertiesAreIgnored(): void
    {
        $this->assertSame([], Config::validate([
            'automaticTests' => null,
            'unknown'        => 'foo bar',
        ]));
    }

    public function testInvalidBoolean(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration property automaticTests must be of type bool');

        $this->assertSame([], Config::validate([
            'automaticTests' => ['invalid type'],
        ]));
    }

    public function testInvalidString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration property mockWith must be of type string');

        $this->assertSame([], Config::validate([
            'mockWith' => ['invalid type'],
        ]));
    }

    public function testInvalidArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration property excludedMethods must be of type array');

        $this->assertSame([], Config::validate([
            'excludedMethods' => 'invalid type',
        ]));
    }

    public function testGetters(): void
    {
        $config = Config::make([
            'automaticTests'    => false,
            'mockWith'          => 'phpunit',
            'generateWith'      => 'custom',
            'baseNamespace'     => 'App\\',
            'baseTestNamespace' => 'App\\Tests\\',
            'excludedMethods'   => [],
            'mergedPhpDoc'      => [],
            'phpDoc'            => ['@author John Doe'],
            'options'           => ['custom' => 'option'],
        ]);

        $this->assertSame(false, $config->hasAutomaticTests());
        $this->assertSame('phpunit', $config->mockWith());
        $this->assertSame('custom', $config->generateWith());
        $this->assertSame('App\\', $config->baseNamespace());
        $this->assertSame('App\\Tests\\', $config->baseTestNamespace());
        $this->assertSame([], $config->excludedMethods());
        $this->assertSame([], $config->mergedPhpDoc());
        $this->assertSame(['@author John Doe'], $config->phpDoc());
        $this->assertSame(['custom' => 'option'], $config->options());
        $this->assertSame('option', $config->getOption('custom'));
        $this->assertSame(null, $config->getOption('unknown'));
        $this->assertSame('foo bar', $config->getOption('unknown', 'foo bar'));
    }
}
