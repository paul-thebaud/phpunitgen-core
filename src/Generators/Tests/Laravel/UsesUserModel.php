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
        return $this->checkAwareAreImplemented()
            ->getImportFactory()
            ->make($class, $this->getUserClassAsString());
    }

    /**
     * Get the Laravel user class as a string.
     *
     * @return string
     */
    protected function getUserClassAsString(): string
    {
        return $this->checkAwareAreImplemented()
            ->getConfig()
            ->getOption('laravel.user', 'App\\User');
    }

    /**
     * Check necessary aware are implemented.
     *
     * @return static|ConfigAware|ImportFactoryAware
     */
    private function checkAwareAreImplemented(): self
    {
        if (! $this instanceof ConfigAware || ! $this instanceof ImportFactoryAware) {
            throw new RuntimeException(
                'trait UsesUserModel must implements ConfigAware and ImportFactoryAware'
            );
        }

        return $this;
    }
}
