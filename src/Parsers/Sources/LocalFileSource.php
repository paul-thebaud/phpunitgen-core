<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Parsers\Sources;

use PhpUnitGen\Core\Contracts\Parsers\Source;
use PhpUnitGen\Core\Exceptions\InvalidArgumentException;

/**
 * Class LocalFileSource.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class LocalFileSource implements Source
{
    /**
     * @var string The source code.
     */
    protected $code;

    /**
     * LocalFileSource constructor.
     *
     * @param string $absolutePath
     */
    public function __construct(string $absolutePath)
    {
        if (! is_file($absolutePath)) {
            throw new InvalidArgumentException(
                "the file at {$absolutePath} does not exists"
            );
        }

        $this->code = file_get_contents($absolutePath);
    }

    /**
     * {@inheritdoc}
     */
    public function toString(): string
    {
        return $this->code;
    }
}
