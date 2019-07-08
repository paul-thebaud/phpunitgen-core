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
    public function testItThrowsAnExceptionWhenKeyIsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration key 0 does not exists');

        new Config(['unknownValue']);
    }

    public function testItThrowsAnExceptionWhenKeyIsUnknown(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration key unknownKey does not exists');

        new Config(['unknownKey' => 'unknownValue']);
    }

    public function testItConstructWithCompleteConfiguration(): void
    {
        $config = new Config([
            'automaticTests'         => false,
            'mockWith'               => 'phpunit',
            'generateWith'           => 'basic',
            'baseNamespace'          => 'App\\',
            'baseTestNamespace'      => '',
            'excludedMethods'        => ['__toString'],
            'mergedPhpDocumentation' => ['author'],
            'phpDocumentation'       => ['@author John Doe'],
        ]);

        $this->assertFalse($config->hasAutomaticTests());
        $this->assertSame('phpunit', $config->getMockWith());
        $this->assertSame('basic', $config->getGenerateWith());
        $this->assertSame('App\\', $config->getBaseNamespace());
        $this->assertSame('', $config->getBaseTestNamespace());
        $this->assertSame(['__toString'], $config->getExcludedMethods());
        $this->assertSame(['author'], $config->getMergedPhpDocumentation());
        $this->assertSame(['@author John Doe'], $config->getPhpDocumentation());
    }

    public function testItCastOnHasAutomaticTest(): void
    {
        $config = new Config(['automaticTests' => '']);

        $this->assertFalse($config->hasAutomaticTests());
    }

    public function testItCastOnGetBaseNamespace(): void
    {
        $config = new Config(['baseNamespace' => null]);

        $this->assertSame('', $config->getBaseNamespace());
    }

    public function testItCastOnGetMockWith(): void
    {
        $config = new Config(['mockWith' => null]);

        $this->assertSame('', $config->getMockWith());
    }

    public function testItCastOnGetGenerateWith(): void
    {
        $config = new Config(['generateWith' => null]);

        $this->assertSame('', $config->getGenerateWith());
    }

    public function testItCastOnGetBaseTestNamespace(): void
    {
        $config = new Config(['baseTestNamespace' => null]);

        $this->assertSame('', $config->getBaseTestNamespace());
    }

    public function testItCastOnGetExcludedMethods(): void
    {
        $config = new Config(['excludedMethods' => null]);

        $this->assertSame([], $config->getExcludedMethods());
    }

    public function testItCastOnGetMergedPhpDocumentation(): void
    {
        $config = new Config(['mergedPhpDocumentation' => null]);

        $this->assertSame([], $config->getMergedPhpDocumentation());
    }

    public function testItCastOnGetPhpDocumentation(): void
    {
        $config = new Config(['phpDocumentation' => null]);

        $this->assertSame([], $config->getPhpDocumentation());
    }
}
