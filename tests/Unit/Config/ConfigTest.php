<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Parsers;

use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use PhpUnitGen\Core\Generators\Tests\DelegateTestGenerator;
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
            'automaticGeneration' => true,
            'implementations'     => DelegateTestGenerator::implementations(),
            'baseNamespace'       => 'App',
            'baseTestNamespace'   => 'Tests',
            'testCase'            => 'Tests\\TestCase',
            'excludedMethods'     => [
                '__construct',
                '__destruct',
            ],
            'mergedPhpDoc'        => [
                'author',
                'copyright',
                'license',
                'version',
            ],
            'phpDoc'              => [],
            'options'             => [
                'context' => 'laravel',
            ],
        ], Config::make()->toArray());
    }

    public function testWhenCompleteConfiguration(): void
    {
        $this->assertSame([
            'automaticGeneration' => false,
            'implementations'     => [],
            'baseNamespace'       => 'App\\',
            'baseTestNamespace'   => 'App\\Tests\\',
            'testCase'            => 'App\\Tests\\TestCase',
            'excludedMethods'     => [],
            'mergedPhpDoc'        => [],
            'phpDoc'              => ['@author John Doe'],
            'options'             => ['custom' => 'option'],
        ], Config::make([
            'automaticGeneration' => false,
            'implementations'     => [],
            'baseNamespace'       => 'App\\',
            'baseTestNamespace'   => 'App\\Tests\\',
            'testCase'            => 'App\\Tests\\TestCase',
            'excludedMethods'     => [],
            'mergedPhpDoc'        => [],
            'phpDoc'              => ['@author John Doe'],
            'options'             => ['custom' => 'option'],
        ])->toArray());
    }

    public function testMissingNullOrInvalidPropertiesAreIgnored(): void
    {
        $this->assertSame([], Config::validate([
            'automaticGeneration' => null,
            'unknown'             => 'foo bar',
        ]));
    }

    public function testInvalidBoolean(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration property automaticGeneration must be of type bool');

        $this->assertSame([], Config::validate([
            'automaticGeneration' => ['invalid type'],
        ]));
    }

    public function testInvalidString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration property baseNamespace must be of type string');

        $this->assertSame([], Config::validate([
            'baseNamespace' => ['invalid type'],
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
            'automaticGeneration' => false,
            'implementations'     => [],
            'baseNamespace'       => 'App\\',
            'baseTestNamespace'   => 'App\\Tests\\',
            'testCase'            => 'App\\Tests\\TestCase',
            'excludedMethods'     => [],
            'mergedPhpDoc'        => [],
            'phpDoc'              => ['@author John Doe'],
            'options'             => ['custom' => 'option'],
        ]);

        $this->assertSame(false, $config->automaticGeneration());
        $this->assertSame([], $config->implementations());
        $this->assertSame('App\\', $config->baseNamespace());
        $this->assertSame('App\\Tests\\', $config->baseTestNamespace());
        $this->assertSame('App\\Tests\\TestCase', $config->testCase());
        $this->assertSame([], $config->excludedMethods());
        $this->assertSame([], $config->mergedPhpDoc());
        $this->assertSame(['@author John Doe'], $config->phpDoc());
        $this->assertSame(['custom' => 'option'], $config->options());
        $this->assertSame('option', $config->getOption('custom'));
        $this->assertSame(null, $config->getOption('unknown'));
        $this->assertSame('foo bar', $config->getOption('unknown', 'foo bar'));
    }
}
