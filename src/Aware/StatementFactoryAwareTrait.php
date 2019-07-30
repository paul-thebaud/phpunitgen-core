<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Aware;

use PhpUnitGen\Core\Contracts\Aware\StatementFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory;

/**
 * Trait StatementFactoryAwareTrait.
 *
 * @see     StatementFactoryAware
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait StatementFactoryAwareTrait
{
    /**
     * @var StatementFactory
     */
    protected $statementFactory;

    /**
     * {@inheritdoc}
     */
    public function setStatementFactory(StatementFactory $statementFactory): void
    {
        $this->statementFactory = $statementFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatementFactory(): StatementFactory
    {
        return $this->statementFactory;
    }
}
