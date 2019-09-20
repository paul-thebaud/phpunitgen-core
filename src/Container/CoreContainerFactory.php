<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Container;

use League\Container\Container;
use PhpUnitGen\Core\Contracts\Config\Config;
use Psr\Container\ContainerInterface;

/**
 * Class CoreContainerFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class CoreContainerFactory
{
    /**
     * Make a container for the given configuration.
     *
     * @param Config $config
     *
     * @return ContainerInterface
     */
    public static function make(Config $config): ContainerInterface
    {
        $container = new Container();
        $container->addServiceProvider(
            new CoreServiceProvider($config)
        );

        return $container;
    }
}
