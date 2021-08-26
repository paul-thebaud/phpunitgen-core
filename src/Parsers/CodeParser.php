<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Parsers;

use PHPStan\BetterReflection\BetterReflection;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use PHPStan\BetterReflection\Reflector\ClassReflector;
use PHPStan\BetterReflection\SourceLocator\Ast\Exception\ParseToAstFailure;
use PHPStan\BetterReflection\SourceLocator\Ast\Locator;
use PHPStan\BetterReflection\SourceLocator\Type\StringSourceLocator;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Contracts\Parsers\Source;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;

/**
 * Class CodeParser.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class CodeParser implements CodeParserContract
{
    /**
     * @var Locator
     */
    protected $astLocator;

    /**
     * CodeParser constructor.
     *
     * @param BetterReflection $betterReflection
     */
    public function __construct(BetterReflection $betterReflection)
    {
        $this->astLocator = $betterReflection->astLocator();
    }

    /**
     * {@inheritdoc}
     */
    public function parse(Source $source): ReflectionClass
    {
        $reflector = new ClassReflector(
            new StringSourceLocator($source->toString(), $this->astLocator)
        );

        try {
            $classes = $reflector->getAllClasses();
        } catch (ParseToAstFailure $exception) {
            throw new InvalidArgumentException(
                'code might have an invalid syntax because AST failed to parse it'
            );
        }

        $classesCount = count($classes);
        if ($classesCount !== 1) {
            throw new InvalidArgumentException(
                'code must contains exactly one class/interface/trait, found '.$classesCount
            );
        }

        return $classes[0];
    }
}
