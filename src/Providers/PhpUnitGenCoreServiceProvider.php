<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Providers;

use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Container\ServiceProvider\AbstractServiceProvider;
use PhpUnitGen\Core\Contracts\CodeParser as CodeParserContract;
use PhpUnitGen\Core\Parsers\CodeParser;
use Roave\BetterReflection\BetterReflection;

/**
 * Class PhpUnitGenCoreServiceProvider.
 *
 * @package PhpUnitGen\Core
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class PhpUnitGenCoreServiceProvider extends AbstractServiceProvider
{
    /**
     * @var string[] $provides
     */
    protected $provides = [
        CodeParserContract::class,
    ];

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        /** @var Container $container */
        $container = $this->getContainer();

        $container->delegate(
            (new ReflectionContainer())->cacheResolutions()
        );

        // Contracts implementations.
        $container->share(CodeParserContract::class, CodeParser::class)
            ->addArgument(BetterReflection::class);
    }
}
