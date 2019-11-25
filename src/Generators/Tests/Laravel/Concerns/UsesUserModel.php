<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel\Concerns;

use PhpUnitGen\Core\Aware\ConfigAwareTrait;
use PhpUnitGen\Core\Aware\ImportFactoryAwareTrait;
use PhpUnitGen\Core\Aware\StatementFactoryAwareTrait;
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
    use ConfigAwareTrait;
    use ImportFactoryAwareTrait;
    use StatementFactoryAwareTrait;

    /**
     * Retrieve the Laravel User model class import.
     *
     * @param TestClass $class
     *
     * @return TestImport
     */
    protected function getUserClass(TestClass $class): TestImport
    {
        return $this->importFactory->make($class, $this->getUserClassAsString());
    }

    /**
     * Get the Laravel user class as a string.
     *
     * @return string
     */
    protected function getUserClassAsString(): string
    {
        return $this->config->getOption('laravel.user', 'App\\User');
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
            $this->statementFactory->makeAffect('user', "new {$userImport}()")
        );
    }
}
