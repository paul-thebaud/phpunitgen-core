<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Parsers;

use PhpUnitGen\Core\Contracts\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Contracts\Source;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use Roave\BetterReflection\BetterReflection;
use Roave\BetterReflection\Reflection\ReflectionClass;
use Roave\BetterReflection\Reflector\ClassReflector;
use Roave\BetterReflection\SourceLocator\Ast\Locator;
use Roave\BetterReflection\SourceLocator\Type\StringSourceLocator;

/**
 * Class CodeParser.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killian.h@live.fr>
 * @license MIT
 */
class CodeParser implements CodeParserContract
{
    /**
     * @var Locator $astLocator
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
     * {@inheritDoc}
     */
    public function parse(Source $source): ReflectionClass
    {
        $reflector = new ClassReflector(
            new StringSourceLocator($source->toString(), $this->astLocator)
        );

        $classes = $reflector->getAllClasses();
        if (count($classes) !== 1) {
            throw new InvalidArgumentException('code contains less or more than one class/interface/trait');
        }

        return $classes[0];
    }
}
