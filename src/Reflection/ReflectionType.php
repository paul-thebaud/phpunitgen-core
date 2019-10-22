<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Reflection;

use Roave\BetterReflection\Reflection\ReflectionType as BetterReflectionType;
use Tightenco\Collect\Support\Collection;

/**
 * Class ReflectionType.
 *
 * This class is a wrapper for a BetterReflection or phpDocumentor type(s).
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class ReflectionType
{
    /**
     * The built in types of PHP.
     */
    protected const BUILT_IN_TYPES = [
        'int',
        'float',
        'string',
        'bool',
        'callable',
        'self',
        'parent',
        'array',
        'iterable',
        'object',
        'void',
    ];

    /**
     * @var string
     */
    protected $type;

    /**
     * @var bool
     */
    protected $nullable;

    /**
     * ReflectionType constructor.
     *
     * @param string $type
     * @param bool   $nullable
     */
    public function __construct(string $type, bool $nullable)
    {
        $this->type = ltrim($type, '\\');
        $this->nullable = $nullable;
    }

    /**
     * Make an instance from a BetterReflection ReflectionType instance.
     *
     * @param BetterReflectionType $reflectionType
     *
     * @return static
     */
    public static function makeForBetterReflectionType(BetterReflectionType $reflectionType): self
    {
        return new self(strval($reflectionType), $reflectionType->allowsNull());
    }

    /**
     * Make an instance from a phpDocumentor string types.
     *
     * @param string[] $types
     *
     * @return static|null
     */
    public static function makeForPhpDocumentorTypes(array $types): ?self
    {
        $stringTypes = new Collection(array_map('strval', $types));

        // Check if its nullable.
        $nullable = $stringTypes->contains('null');
        // Reject null and mixed types, since they are not "real" types.
        $stringTypes = $stringTypes->reject('null')->reject('mixed');

        if ($stringTypes->isEmpty()) {
            return null;
        }

        return new self($stringTypes->first(), $nullable);
    }

    /**
     * Get the type as a string.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Tells if this type is nullable.
     *
     * @return bool
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Tells if this type is built in.
     *
     * @return bool
     */
    public function isBuiltIn(): bool
    {
        return in_array($this->type, self::BUILT_IN_TYPES);
    }
}
