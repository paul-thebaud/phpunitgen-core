<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Helpers;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Types\Context;
use phpDocumentor\Reflection\Types\ContextFactory;
use PhpUnitGen\Core\Reflection\ReflectionType;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflection\ReflectionMethod;
use Roave\BetterReflection\Reflection\ReflectionParameter;
use Roave\BetterReflection\Reflection\ReflectionProperty;
use Tightenco\Collect\Support\Collection;

/**
 * Class Reflect.
 *
 * Helper methods for reflection.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class Reflect
{
    /**
     * @var DocBlockFactoryInterface The doc block factory to parse doc blocks.
     */
    protected static $docBlockFactory;

    /**
     * @return DocBlockFactoryInterface
     */
    protected static function getDocBlockFactory(): DocBlockFactoryInterface
    {
        return self::$docBlockFactory ?? DocBlockFactory::createInstance();
    }

    /**
     * @param DocBlockFactoryInterface $docBlockFactory
     */
    public static function setDocBlockFactory(?DocBlockFactoryInterface $docBlockFactory): void
    {
        self::$docBlockFactory = $docBlockFactory;
    }

    /**
     * Get the immediate methods for the given reflection class.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return ReflectionMethod[]|Collection
     */
    public static function methods(ReflectionClass $reflectionClass): Collection
    {
        return new Collection($reflectionClass->getImmediateMethods());
    }

    /**
     * Get the immediate method matching the given name.
     *
     * @param ReflectionClass $reflectionClass
     * @param string          $name
     *
     * @return ReflectionMethod|null
     */
    public static function method(ReflectionClass $reflectionClass, string $name): ?ReflectionMethod
    {
        return self::methods($reflectionClass)
            ->first(function (ReflectionMethod $reflectionMethod) use ($name) {
                return $reflectionMethod->getShortName() === $name;
            });
    }

    /**
     * Get the properties for the given reflection class.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return ReflectionProperty[]|Collection
     */
    public static function properties(ReflectionClass $reflectionClass): Collection
    {
        return new Collection($reflectionClass->getImmediateProperties());
    }

    /**
     * Get the immediate property matching the given name.
     *
     * @param ReflectionClass $reflectionClass
     * @param string          $name
     *
     * @return ReflectionProperty|null
     */
    public static function property(ReflectionClass $reflectionClass, string $name): ?ReflectionProperty
    {
        return self::properties($reflectionClass)
            ->first(function (ReflectionProperty $reflectionProperty) use ($name) {
                return $reflectionProperty->getName() === $name;
            });
    }

    /**
     * Get the parameters for the given reflection method.
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return ReflectionParameter[]|Collection
     */
    public static function parameters(ReflectionMethod $reflectionMethod): Collection
    {
        return new Collection($reflectionMethod->getParameters());
    }

    /**
     * Get the parameter type using Reflection or DocBlock.
     *
     * @param ReflectionParameter $reflectionParameter
     *
     * @return ReflectionType|null
     */
    public static function parameterType(ReflectionParameter $reflectionParameter): ?ReflectionType
    {
        return ReflectionType::make(
            $reflectionParameter->getType(),
            self::convertDocBlockTagToTypes(
                self::docBlockTags($reflectionParameter->getDeclaringFunction())
                    ->first(function ($paramTag) use ($reflectionParameter) {
                        return $paramTag instanceof DocBlock\Tags\Param
                            && $paramTag->getVariableName() === $reflectionParameter->getName();
                    })
            )
        );
    }

    /**
     * Get the return type of a method using Reflection or DocBlock.
     *
     * @param ReflectionMethod $reflectionMethod
     *
     * @return ReflectionType|null
     */
    public static function returnType(ReflectionMethod $reflectionMethod): ?ReflectionType
    {
        return ReflectionType::make(
            $reflectionMethod->getReturnType(),
            self::convertDocBlockTagToTypes(
                self::docBlockTags($reflectionMethod)
                    ->first(function ($returnTag) {
                        return $returnTag instanceof DocBlock\Tags\Return_;
                    })
            )
        );
    }

    /**
     * Get the doc block object from the given reflection object (might be a class, method...).
     *
     * @param ReflectionMethod|ReflectionClass $reflectionObject
     *
     * @return DocBlock|null
     */
    public static function docBlock(ReflectionMethod|ReflectionClass $reflectionObject): ?DocBlock
    {
        $docComment = $reflectionObject->getDocComment() ?? '';

        return $docComment !== ''
            ? self::getDocBlockFactory()->create($docComment, self::docBlockContext($reflectionObject))
            : null;
    }

    /**
     * Get the doc block tags from the given reflection object (might be a class, method...).
     *
     * @param ReflectionMethod|ReflectionClass $reflectionObject
     *
     * @return Collection
     */
    public static function docBlockTags(ReflectionMethod|ReflectionClass $reflectionObject): Collection
    {
        $docBlock = self::docBlock($reflectionObject);

        return new Collection($docBlock ? $docBlock->getTags() : []);
    }

    /**
     * Convert the doc block tag to an array of types.
     *
     * @param DocBlock\Tags\TagWithType|null $tag
     *
     * @return array
     */
    private static function convertDocBlockTagToTypes(?DocBlock\Tags\TagWithType $tag): array
    {
        return $tag ? explode('|', (string) $tag->getType()) : [];
    }

    /**
     * Build the doc block parsing context from a reflection object.
     *
     * @param ReflectionMethod|ReflectionClass $reflectionObject
     *
     * @return Context
     */
    private static function docBlockContext(ReflectionMethod|ReflectionClass $reflectionObject): Context
    {
        $reflectionClass = $reflectionObject instanceof ReflectionMethod
            ? $reflectionObject->getDeclaringClass()
            : $reflectionObject;

        $contextFactory = new ContextFactory();

        return $contextFactory->createForNamespace(
            $reflectionClass->getNamespaceName() ?? '',
            $reflectionClass->getLocatedSource()->getSource(),
        );
    }
}
