<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

/**
 * Class TestCase.
 */
class TestCase extends PHPUnitTestCase
{
    use MockeryPHPUnitIntegration;
}
