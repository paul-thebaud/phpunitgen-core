<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Sources;

use PhpUnitGen\Core\Contracts\Source;

/**
 * Class StringSource.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killian.h@live.fr>
 * @license MIT
 */
class StringSource implements Source
{
    /**
     * @var string $code The source code.
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
     * {@inheritDoc}
     */
    public function toString(): string
    {
        return $this->code;
    }
}
