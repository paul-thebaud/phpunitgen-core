<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Config;

use PhpUnitGen\Core\Contracts\Config\Config as ConfigContract;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;

/**
 * Class Config.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class Config implements ConfigContract
{
    /**
     * The properties of the config with there type hint.
     */
    protected const PROPERTIES = [
        'automaticTests'    => 'bool',
        'mockWith'          => 'string',
        'generateWith'      => 'string',
        'baseNamespace'     => 'string',
        'baseTestNamespace' => 'string',
        'testCase'          => 'string',
        'excludedMethods'   => 'array',
        'mergedPhpDoc'      => 'array',
        'phpDoc'            => 'array',
        'options'           => 'array',
    ];

    /**
     * @var array The configuration, as an array.
     */
    protected $config;

    /**
     * Validate the given config and create a new instance.
     *
     * @param array $config
     *
     * @return static
     */
    public static function make(array $config = []): self
    {
        $config = static::validate($config);

        return new static(
            array_merge(
                require __DIR__.'/default.php',
                $config
            )
        );
    }

    /**
     * Validate the given config properties and the cleaned config array.
     *
     * @param array $config
     *
     * @return array
     */
    public static function validate(array $config): array
    {
        $validated = [];

        foreach (static::PROPERTIES as $property => $type) {
            $value = $config[$property] ?? null;

            if ($value === null) {
                continue;
            }

            if (! call_user_func('is_'.$type, $value)) {
                throw new InvalidArgumentException(
                    "configuration property {$property} must be of type {$type}"
                );
            }

            $validated[$property] = $value;
        }

        return $validated;
    }

    /**
     * Config constructor.
     *
     * @param array $config
     */
    protected function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function hasAutomaticTests(): bool
    {
        return $this->config['automaticTests'];
    }

    /**
     * {@inheritdoc}
     */
    public function mockWith(): string
    {
        return $this->config['mockWith'];
    }

    /**
     * {@inheritdoc}
     */
    public function generateWith(): string
    {
        return $this->config['generateWith'];
    }

    /**
     * {@inheritdoc}
     */
    public function baseNamespace(): string
    {
        return $this->config['baseNamespace'];
    }

    /**
     * {@inheritdoc}
     */
    public function baseTestNamespace(): string
    {
        return $this->config['baseTestNamespace'];
    }

    /**
     * {@inheritdoc}
     */
    public function testCase(): string
    {
        return $this->config['testCase'];
    }

    /**
     * {@inheritdoc}
     */
    public function excludedMethods(): array
    {
        return $this->config['excludedMethods'];
    }

    /**
     * {@inheritdoc}
     */
    public function mergedPhpDoc(): array
    {
        return $this->config['mergedPhpDoc'];
    }

    /**
     * {@inheritdoc}
     */
    public function phpDoc(): array
    {
        return $this->config['phpDoc'];
    }

    /**
     * {@inheritdoc}
     */
    public function options(): array
    {
        return $this->config['options'];
    }

    /**
     * {@inheritdoc}
     */
    public function getOption(string $name, $default = null)
    {
        return $this->config['options'][$name] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->config;
    }
}
