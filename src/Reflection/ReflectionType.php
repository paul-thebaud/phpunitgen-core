<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Reflection;

use PhpUnitGen\Core\Helpers\Str;
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
        'mixed',
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
        $clearedType = ltrim($type, '\\');
        if (Str::contains('|', $clearedType)) {
            $clearedType = explode('|', $clearedType)[0];
        }

        $this->type = $clearedType;
        $this->nullable = $nullable;
    }

    /**
     * Make a type from the native or doc type.
     *
     * @param BetterReflectionType|null $reflectionType
     * @param array                     $docTypes
     *
     * @return static|null
     */
    public static function make(?BetterReflectionType $reflectionType, array $docTypes): ?self
    {
        $type = null;
        if ($reflectionType) {
            $type = static::makeForBetterReflectionType($reflectionType);

            // When native type is not precisely defined (mixed or object),
            // try to retrieve the doc type instead.
            if ($type->getType() !== 'mixed' && $type->getType() !== 'object') {
                return $type;
            }
        }

        return static::makeForPhpDocumentorTypes($docTypes) ?? $type;
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
        // Reject null type, since it is not a "real" types.
        $stringTypes = $stringTypes->reject('null');

        if ($stringTypes->isEmpty()) {
            return null;
        }

        $stringType = $stringTypes->first();
        if (Str::endsWith('[]', $stringType)) {
            $stringType = 'array';
        }

        return new self($stringType, $nullable);
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
