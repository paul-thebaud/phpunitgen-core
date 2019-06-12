<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Parsers;

use PhpUnitGen\Core\Contracts\Parsers\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Contracts\Parsers\Source;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Type\StringSourceLocator;

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

        $classes = $reflector->getAllClasses();
        $classesCount = count($classes);
        if ($classesCount !== 1) {
            throw new InvalidArgumentException(
                "code must contains exactly one class/interface/trait, found {$classesCount}"
            );
        }

        return $classes[0];
    }
}
