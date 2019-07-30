<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel;

use PhpUnitGen\Core\Contracts\Aware\ConfigAware;
use PhpUnitGen\Core\Contracts\Aware\ImportFactoryAware;
use PhpUnitGen\Core\Exceptions\RuntimeException;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;

/**
 * Trait UsesUserModel.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait UsesUserModel
{
    /**
     * Retrieve the Laravel User model class import.
     *
     * @param TestClass $class
     *
     * @return TestImport
     */
    protected function getUserClass(TestClass $class): TestImport
    {
        if ($this instanceof ConfigAware && $this instanceof ImportFactoryAware) {
            return $this->getImportFactory()->make(
                $class,
                $this->getConfig()->getOption('laravel.user', 'App\\User')
            );
        }

        throw new RuntimeException(
            'trait UsesUserModel must implements ConfigAware and ImportFactoryAware'
        );
    }
}
