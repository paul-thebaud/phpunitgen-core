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
     * The type for string properties.
     */
    protected const TYPE_STRING = 'string';

    /**
     * The type for boolean properties.
     */
    protected const TYPE_BOOL = 'bool';

    /**
     * The type for array properties.
     */
    protected const TYPE_ARRAY = 'array';

    /**
     * The properties of the config with there type hint.
     */
    protected const PROPERTIES = [
        'automaticGeneration'      => self::TYPE_BOOL,
        'implementations'          => self::TYPE_ARRAY,
        'baseNamespace'            => self::TYPE_STRING,
        'baseTestNamespace'        => self::TYPE_STRING,
        'testCase'                 => self::TYPE_STRING,
        'testClassFinal'           => self::TYPE_BOOL,
        'testClassStrictTypes'     => self::TYPE_BOOL,
        'testClassTypedProperties' => self::TYPE_BOOL,
        'excludedMethods'          => self::TYPE_ARRAY,
        'mergedPhpDoc'             => self::TYPE_ARRAY,
        'phpDoc'                   => self::TYPE_ARRAY,
        'phpHeaderDoc'             => self::TYPE_STRING,
        'options'                  => self::TYPE_ARRAY,
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
    public static function make(array $config = [])
    {
        $config = static::validate($config);

        return new static(
            array_merge(
                static::getDefaultConfig(),
                $config,
            ),
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
                    "configuration property {$property} must be of type {$type}",
                );
            }

            $validated[$property] = $value;
        }

        return $validated;
    }

    /**
     * Retrieve the default configuration as an array.
     *
     * @return array
     */
    protected static function getDefaultConfig(): array
    {
        return require __DIR__.'/../../config/phpunitgen.php';
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
    public function automaticGeneration(): bool
    {
        return $this->config['automaticGeneration'];
    }

    /**
     * {@inheritdoc}
     */
    public function implementations(): array
    {
        return $this->config['implementations'];
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
    public function testClassFinal(): bool
    {
        return $this->config['testClassFinal'];
    }

    /**
     * {@inheritdoc}
     */
    public function testClassStrictTypes(): bool
    {
        return $this->config['testClassStrictTypes'];
    }

    /**
     * {@inheritdoc}
     */
    public function testClassTypedProperties(): bool
    {
        return $this->config['testClassTypedProperties'];
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
    public function phpHeaderDoc(): string
    {
        return $this->config['phpHeaderDoc'];
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
        return $this->options()[$name] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return $this->config;
    }
}
