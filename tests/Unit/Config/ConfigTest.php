<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Config;

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
        self::assertSame([
            'automaticGeneration'      => true,
            'implementations'          => DelegateTestGenerator::implementations(),
            'baseNamespace'            => 'App',
            'baseTestNamespace'        => 'Tests',
            'testCase'                 => 'Tests\\TestCase',
            'testClassFinal'           => true,
            'testClassStrictTypes'     => false,
            'testClassTypedProperties' => true,
            'excludedMethods'          => [
                '__construct',
                '__destruct',
            ],
            'mergedPhpDoc'             => [
                'author',
                'copyright',
                'license',
                'version',
            ],
            'phpDoc'                   => [],
            'phpHeaderDoc'             => '',
            'options'                  => [
                'context' => 'laravel',
            ],
        ], Config::make()->toArray());
    }

    public function testWhenCompleteConfiguration(): void
    {
        self::assertSame([
            'automaticGeneration'      => false,
            'implementations'          => [],
            'baseNamespace'            => 'App\\',
            'baseTestNamespace'        => 'App\\Tests\\',
            'testCase'                 => 'App\\Tests\\TestCase',
            'testClassFinal'           => false,
            'testClassStrictTypes'     => true,
            'testClassTypedProperties' => false,
            'excludedMethods'          => [],
            'mergedPhpDoc'             => [],
            'phpDoc'                   => ['@author John Doe'],
            'phpHeaderDoc'             => "/*\n * @license MIT\n */",
            'options'                  => ['custom' => 'option'],
        ], Config::make([
            'automaticGeneration'      => false,
            'implementations'          => [],
            'baseNamespace'            => 'App\\',
            'baseTestNamespace'        => 'App\\Tests\\',
            'testCase'                 => 'App\\Tests\\TestCase',
            'testClassFinal'           => false,
            'testClassStrictTypes'     => true,
            'testClassTypedProperties' => false,
            'excludedMethods'          => [],
            'mergedPhpDoc'             => [],
            'phpDoc'                   => ['@author John Doe'],
            'phpHeaderDoc'             => "/*\n * @license MIT\n */",
            'options'                  => ['custom' => 'option'],
        ])->toArray());
    }

    public function testMissingNullOrInvalidPropertiesAreIgnored(): void
    {
        self::assertSame([], Config::validate([
            'automaticGeneration' => null,
            'unknown'             => 'foo bar',
        ]));
    }

    public function testInvalidBoolean(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration property automaticGeneration must be of type bool');

        self::assertSame([], Config::validate([
            'automaticGeneration' => ['invalid type'],
        ]));
    }

    public function testInvalidString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration property baseNamespace must be of type string');

        self::assertSame([], Config::validate([
            'baseNamespace' => ['invalid type'],
        ]));
    }

    public function testInvalidArray(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration property excludedMethods must be of type array');

        self::assertSame([], Config::validate([
            'excludedMethods' => 'invalid type',
        ]));
    }

    public function testGetters(): void
    {
        $config = Config::make([
            'automaticGeneration'  => false,
            'implementations'      => [],
            'baseNamespace'        => 'App\\',
            'baseTestNamespace'    => 'App\\Tests\\',
            'testCase'             => 'App\\Tests\\TestCase',
            'testClassFinal'       => false,
            'testClassStrictTypes' => true,
            'excludedMethods'      => [],
            'mergedPhpDoc'         => [],
            'phpDoc'               => ['@author John Doe'],
            'phpHeaderDoc'         => "/*\n * @license MIT\n */",
            'options'              => ['custom' => 'option'],
        ]);

        self::assertSame(false, $config->automaticGeneration());
        self::assertSame([], $config->implementations());
        self::assertSame('App\\', $config->baseNamespace());
        self::assertSame('App\\Tests\\', $config->baseTestNamespace());
        self::assertSame('App\\Tests\\TestCase', $config->testCase());
        self::assertSame(false, $config->testClassFinal());
        self::assertSame(true, $config->testClassStrictTypes());
        self::assertSame([], $config->excludedMethods());
        self::assertSame([], $config->mergedPhpDoc());
        self::assertSame(['@author John Doe'], $config->phpDoc());
        self::assertSame("/*\n * @license MIT\n */", $config->phpHeaderDoc());
        self::assertSame(['custom' => 'option'], $config->options());
        self::assertSame('option', $config->getOption('custom'));
        self::assertSame(null, $config->getOption('unknown'));
        self::assertSame('foo bar', $config->getOption('unknown', 'foo bar'));
    }
}
