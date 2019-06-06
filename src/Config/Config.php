<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Config;

use PhpUnitGen\Core\Exceptions\InvalidArgumentException;

/**
 * Class Config.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killian.h@live.fr>
 * @license MIT
 */
class Config
{
    /**
     * @var bool $automaticConstruct If tested class's constructor should be invoked in "setUp" with mocked parameters.
     */
    protected $automaticConstruct = true;

    /**
     * @var bool $automaticTests If tests should be generated (getter, setter...).
     */
    protected $automaticTests = true;

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
                throw new InvalidArgumentException("configuration key {$configurationKey} does not exists");
            }

            $this->{$configurationKey} = $configurationValue;
        }
    }

    /**
     * Check if this config allow automatic construct.
     *
     * @return bool
     */
    public function hasAutomaticConstruct(): bool
    {
        return (bool) $this->automaticConstruct;
    }

    /**
     * Check if this config allow automatic tests.
     *
     * @return bool
     */
    public function hasAutomaticTests(): bool
    {
        return (bool) $this->automaticTests;
    }

    /**
     * Get the base namespace for test.
     *
     * @return string
     */
    public function getBaseTestNamespace(): string
    {
        return (string) $this->baseTestNamespace;
    }

    /**
     * Get the PHP documentation for test.
     *
     * @return array
     */
    public function getPhpDocumentation(): array
    {
        return (array) $this->phpDocumentation;
    }
}
