<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Laravel\Concerns;

use Mockery;
use PhpUnitGen\Core\Exceptions\RuntimeException;
use PhpUnitGen\Core\Generators\Tests\Laravel\Concerns\UsesUserModel;
use PhpUnitGen\Core\Models\TestClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class UsesUserModelTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\Laravel\Concerns\UsesUserModel
 */
class UsesUserModelTest extends TestCase
{
    use UsesUserModel;

    public function testGetUserClassWhenNotImplementingInterfaces(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            'trait UsesUserModel must have ConfigAware, ImportFactoryAware and StatementFactoryAware implemented'
        );

        $this->getUserClass(Mockery::mock(TestClass::class));
    }
}