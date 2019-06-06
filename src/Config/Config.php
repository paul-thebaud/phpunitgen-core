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
     * Generate mock construction with Mockery.
     *
     * @see https://github.com/mockery/mockery
     */
    public const MOCK_WITH_MOCKERY = 'mockery';

    /**
     * Generate mock construction with PHPUnit.
     *
     * @see https://github.com/sebastianbergmann/phpunit
     */
    public const MOCK_WITH_PHPUNIT = 'phpunit';

    /**
     * @var bool $automaticTests If instantiation and tests (getter, setter...) should be generated.
     */
    protected $automaticTests = true;

    /**
     * @var string $mockWith Tells which library should be used to generate mock construction.
     */
    protected $mockWith = self::MOCK_WITH_MOCKERY;

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
     * Check if this config allow automatic tests.
     *
     * @return bool
     */
    public function hasAutomaticTests(): bool
    {
        return (bool) $this->automaticTests;
    }

    /**
     * Get the mocking library to use.
     *
     * @return string
     */
    public function getMockWith(): string
    {
        return (string) $this->mockWith;
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
