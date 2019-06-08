<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Config;

use PhpUnitGen\Core\Exceptions\InvalidArgumentException;

/**
 * Class Config.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class Config
{
    /**
     * @var bool $automaticTests If instantiation and tests (getter, setter...) should be generated.
     */
    protected $automaticTests = true;

    /**
     * @var string $mockWith The generator should be used to generate mock construction.
     */
    protected $mockWith;

    /**
     * @var string $generateWith The generator should be used to test class.
     */
    protected $generateWith;

    /**
     * @var string $baseNamespace The base namespace of source code.
     */
    protected $baseNamespace = '';

    /**
     * @var string $baseTestNamespace The base namespace for the test class.
     */
    protected $baseTestNamespace = 'Tests\\';

    /**
     * @var array $phpDocumentation The PHP documentation for the test class.
     */
    protected $phpDocumentation = [];

    /**
     * Config constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration = [])
    {
        foreach ($configuration as $configurationKey => $configurationValue) {
            if (! is_string($configurationKey) || ! property_exists($this, $configurationKey)) {
                throw new InvalidArgumentException(
                    "configuration key {$configurationKey} does not exists"
                );
            }

            $this->{$configurationKey} = $configurationValue;
        }
    }

    /**
     * @return bool
     */
    public function hasAutomaticTests(): bool
    {
        return (bool) $this->automaticTests;
    }

    /**
     * @return string
     */
    public function getMockWith(): string
    {
        return (string) $this->mockWith;
    }

    /**
     * @return string
     */
    public function getGenerateWith(): string
    {
        return (string) $this->generateWith;
    }

    /**
     * @return string
     */
    public function getBaseNamespace(): string
    {
        return (string) $this->baseNamespace;
    }

    /**
     * @return string
     */
    public function getBaseTestNamespace(): string
    {
        return (string) $this->baseTestNamespace;
    }

    /**
     * @return array
     */
    public function getPhpDocumentation(): array
    {
        return (array) $this->phpDocumentation;
    }
}
