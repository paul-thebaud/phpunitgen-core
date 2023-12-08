<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Config;

/**
 * Interface Config.
 *
 * A configuration to change behaviors of PhpUnitGen components.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface Config
{
    /**
     * Tells if the generator should create advanced tests skeletons and class instantiation.
     *
     * @return bool
     */
    public function automaticGeneration(): bool;

    /**
     * Get the contracts implementations mapping.
     *
     * @return array
     */
    public function implementations(): array;

    /**
     * Get the base namespace of the source code. This will be removed from the test class namespace.
     *
     * @return string
     */
    public function baseNamespace(): string;

    /**
     * Get the base namespace of the tests code. This will be prepended to the test class namespace.
     *
     * @return string
     */
    public function baseTestNamespace(): string;

    /**
     * Get the test case absolute class name.
     *
     * @return string
     */
    public function testCase(): string;

    /**
     * Tells if the test class should be final.
     *
     * @return bool
     */
    public function testClassFinal(): bool;

    /**
     * Tells if the test class should declare strict types.
     *
     * @return bool
     */
    public function testClassStrictTypes(): bool;

    /**
     * Tells if the test class properties should be typed or documented.
     *
     * @return bool
     */
    public function testClassTypedProperties(): bool;

    /**
     * Get the case insensitive RegExp (without opening and closing "/") that tested methods shouldn't match.
     *
     * @return array
     */
    public function excludedMethods(): array;

    /**
     * Get the PHP documentation tags that should be retrieved from tested class and append in its documentation.
     *
     * @return array
     */
    public function mergedPhpDoc(): array;

    /**
     * Get the PHP documentation lines that should always be added to the tested class documentation.
     *
     * @return array
     */
    public function phpDoc(): array;

    /**
     * Get the PHP header documentation lines that should always be added to the generated file documentation.
     *
     * @return string
     */
    public function phpHeaderDoc(): string;

    /**
     * Get the additional options which might be used by specific test generators.
     *
     * @return array
     */
    public function options(): array;

    /**
     * Get an additional option using its name. Returns $default if the option is not defined.
     *
     * @param string $name
     * @param null   $default
     *
     * @return mixed
     */
    public function getOption(string $name, $default = null);

    /**
     * Get the array version of the config.
     *
     * @return array
     */
    public function toArray(): array;
}
