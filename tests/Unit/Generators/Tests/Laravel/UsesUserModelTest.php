<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit\Generators\Tests\Laravel;

use Mockery;
use PhpUnitGen\Core\Exceptions\RuntimeException;
use PhpUnitGen\Core\Generators\Tests\Laravel\UsesUserModel;
use PhpUnitGen\Core\Models\TestClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class UsesUserModelTest.
 *
 * @covers \PhpUnitGen\Core\Generators\Tests\Laravel\UsesUserModel
 */
class UsesUserModelTest extends TestCase
{
    use UsesUserModel;

    public function testGetUserClassWhenNotImplementingInterfaces(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('trait UsesUserModel must have ConfigAware and ImportFactoryAware implemented');

        $this->getUserClass(Mockery::mock(TestClass::class));
    }
}
