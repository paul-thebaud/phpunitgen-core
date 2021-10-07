<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Parsers;

use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Interface CodeParser.
 *
 * An object which can parse a code to retrieve a ReflectionClass.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface CodeParser
{
    /**
     * Parse a code source and build the ReflectionClass from it.
     *
     * @param Source $source
     *
     * @return ReflectionClass
     *
     * @throws InvalidArgumentException
     */
    public function parse(Source $source): ReflectionClass;
}
