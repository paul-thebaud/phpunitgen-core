<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel;

use PhpUnitGen\Core\Contracts\Aware\ConfigAware;
use PhpUnitGen\Core\Contracts\Aware\ImportFactoryAware;
use PhpUnitGen\Core\Contracts\Aware\StatementFactoryAware;
use PhpUnitGen\Core\Exceptions\RuntimeException;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestMethod;

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
     * Make the user affect statement.
     *
     * @param TestClass  $class
     * @param TestMethod $method
     */
    protected function makeUserAffectStatement(TestClass $class, TestMethod $method): void
    {
        $userImport = $this->getUserClass($class)->getFinalName();
        $method->addStatement(
            $this->checkAwareAreImplemented()->getStatementFactory()->makeAffect('user', "new {$userImport}()")
        );
    }

    /**
     * Check necessary aware are implemented.
     *
     * @return static|ConfigAware|ImportFactoryAware|StatementFactoryAware
     */
    private function checkAwareAreImplemented(): self
    {
        if (! $this instanceof ConfigAware
            || ! $this instanceof ImportFactoryAware
            || ! $this instanceof StatementFactoryAware
        ) {
            throw new RuntimeException(
                'trait UsesUserModel must have ConfigAware, ImportFactoryAware and StatementFactoryAware implemented'
            );
        }

        return $this;
    }
}
