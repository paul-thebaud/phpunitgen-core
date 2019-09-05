<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Aware;

use PhpUnitGen\Core\Contracts\Generators\Factories\StatementFactory;

/**
 * Interface StatementFactoryAware.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface StatementFactoryAware
{
    /**
     * @return StatementFactory
     */
    public function getStatementFactory(): StatementFactory;

    /**
     * @param StatementFactory $config
     */
    public function setStatementFactory(StatementFactory $config): void;
}
