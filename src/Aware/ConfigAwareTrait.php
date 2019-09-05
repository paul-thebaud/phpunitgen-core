<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Aware;

use PhpUnitGen\Core\Contracts\Aware\ConfigAware;
use PhpUnitGen\Core\Contracts\Config\Config;

/**
 * Trait ConfigAwareTrait.
 *
 * @see     ConfigAware
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait ConfigAwareTrait
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * {@inheritdoc}
     */
    public function setConfig(Config $config): void
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig(): Config
    {
        return $this->config;
    }
}
