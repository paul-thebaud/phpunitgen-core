<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Aware;

use PhpUnitGen\Core\Contracts\Config\Config;

/**
 * Interface ConfigAware.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface ConfigAware
{
    /**
     * @return Config
     */
    public function getConfig(): Config;

    /**
     * @param Config $config
     */
    public function setConfig(Config $config): void;
}
