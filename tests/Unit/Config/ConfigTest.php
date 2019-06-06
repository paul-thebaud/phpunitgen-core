<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Tests\Unit\Parsers;

use PHPUnit\Framework\TestCase;
use PhpUnitGen\Core\Config\Config;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;

/**
 * Class ConfigTest.
 *
 * @covers Config
 */
class ConfigTest extends TestCase
{
    public function testWhenInvalidKeyGiven(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration key 0 does not exists');

        new Config(['unknownValue']);
    }

    public function testWhenUnknownKeyGiven(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('configuration key unknownKey does not exists');

        new Config(['unknownKey' => 'unknownValue']);
    }

    public function testWhenCompleteConfigurationGiven(): void
    {
        $config = new Config([
            'automaticConstruct' => false,
            'automaticTests'     => false,
            'baseTestNamespace'  => '',
            'phpDocumentation'   => ['author' => 'John Doe'],
        ]);

        $this->assertFalse($config->hasAutomaticConstruct());
        $this->assertFalse($config->hasAutomaticTests());
        $this->assertSame('', $config->getBaseTestNamespace());
        $this->assertSame(['author' => 'John Doe'], $config->getPhpDocumentation());
    }

    public function testWhenAutomaticConstructIsCasted(): void
    {
        $config = new Config(['automaticConstruct' => '']);

        $this->assertFalse($config->hasAutomaticConstruct());
    }

    public function testWhenAutomaticTestIsCasted(): void
    {
        $config = new Config(['automaticTests' => '']);

        $this->assertFalse($config->hasAutomaticTests());
    }

    public function testWhenBaseTestNamespaceIsCasted(): void
    {
        $config = new Config(['baseTestNamespace' => null]);

        $this->assertSame('', $config->getBaseTestNamespace());
    }

    public function testWhenPhpDocumentationIsCasted(): void
    {
        $config = new Config(['phpDocumentation' => null]);

        $this->assertSame([], $config->getPhpDocumentation());
    }
}
