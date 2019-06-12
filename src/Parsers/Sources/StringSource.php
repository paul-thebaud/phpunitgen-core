<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Parsers\Sources;

use PhpUnitGen\Core\Contracts\Parsers\Source;

/**
 * Class StringSource.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class StringSource implements Source
{
    /**
     * @var string The source code.
     */
    protected $code;

    /**
     * StringSource constructor.
     *
     * @param string $code
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return $this->code;
    }
}
