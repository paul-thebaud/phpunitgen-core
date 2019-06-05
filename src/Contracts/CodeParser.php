<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts;

use PhpUnitGen\Core\Exceptions\InvalidArgumentException;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Interface CodeParser.
 *
 * Methods for code parsing.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killian.h@live.fr>
 * @license MIT
 */
interface CodeParser
{
    /**
     * Parse a code source and build the ReflectionClass from it.
     *
     * @param string $code
     *
     * @return ReflectionClass
     *
     * @throws InvalidArgumentException
     */
    public function parse(string $code): ReflectionClass;
}
