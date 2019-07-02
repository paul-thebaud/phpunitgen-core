<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Config;

use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use Tightenco\Collect\Support\Collection;

/**
 * Class Config.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class Config
{
    /**
     * @var bool If instantiation and tests (getter, setter...) should be generated.
     */
    protected $automaticTests = true;

    /**
     * @var string The generator should be used to generate mock construction.
     */
    protected $mockWith = 'mockery';

    /**
     * @var string The generator should be used to test class.
     */
    protected $generateWith = 'basic';

    /**
     * @var string The base namespace of source code.
     */
    protected $baseNamespace = '';

    /**
     * @var string The base namespace for the test class.
     */
    protected $baseTestNamespace = 'Tests\\';

    /**
     * @var string[] The insensitive regex tested methods shouldn't match.
     */
    protected $excludedMethods = [
        '__construct',
        '__destruct',
    ];

    /**
     * @var string[] The PHP documentation for the test class.
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
     * @return string[]
     */
    public function getExcludedMethods(): array
    {
        return (array) $this->excludedMethods;
    }

    /**
     * @return string[]|Collection
     */
    public function getPhpDocumentation(): Collection
    {
        return new Collection((array) $this->phpDocumentation);
    }
}
