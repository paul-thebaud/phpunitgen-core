<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Unit;

use Mockery;
use PhpUnitGen\Core\Contracts\Config\Config;
use PhpUnitGen\Core\Contracts\Generators\TestGenerator;
use PhpUnitGen\Core\Contracts\Parsers\CodeParser;
use PhpUnitGen\Core\Contracts\Parsers\Source;
use PhpUnitGen\Core\Contracts\Renderers\Rendered;
use PhpUnitGen\Core\Contracts\Renderers\Renderer;
use PhpUnitGen\Core\CoreApplication;
use PhpUnitGen\Core\Models\TestClass;
use Psr\Container\ContainerInterface;
use PHPStan\BetterReflection\Reflection\ReflectionClass;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class CoreApplicationTest.
 *
 * @covers \PhpUnitGen\Core\CoreApplication
 */
class CoreApplicationTest extends TestCase
{
    public function testMakeUsesGivenConfig(): void
    {
        $application = CoreApplication::make([
            'automaticGeneration' => false,
        ]);

        /** @var Config $config */
        $config = $application->getContainer()->get(Config::class);

        $this->assertFalse($config->automaticGeneration());
    }

    public function testRunExecutesAllPhpUnitGenTasks(): void
    {
        $container = Mockery::mock(ContainerInterface::class);
        $codeParser = Mockery::mock(CodeParser::class);
        $testGenerator = Mockery::mock(TestGenerator::class);
        $renderer = Mockery::mock(Renderer::class);
        $source = Mockery::mock(Source::class);
        $reflectionClass = Mockery::mock(ReflectionClass::class);
        $testClass = Mockery::mock(TestClass::class);
        $rendered = Mockery::mock(Rendered::class);

        $application = new CoreApplication($container);

        $container->shouldReceive('get')
            ->once()
            ->with(CodeParser::class)
            ->andReturn($codeParser);
        $container->shouldReceive('get')
            ->once()
            ->with(TestGenerator::class)
            ->andReturn($testGenerator);
        $container->shouldReceive('get')
            ->once()
            ->with(Renderer::class)
            ->andReturn($renderer);

        $codeParser->shouldReceive('parse')
            ->once()
            ->with($source)
            ->andReturn($reflectionClass);
        $testGenerator->shouldReceive('generate')
            ->once()
            ->with($reflectionClass)
            ->andReturn($testClass);
        $renderer->shouldReceive('visitTestClass')
            ->once()
            ->with($testClass)
            ->andReturnSelf();
        $renderer->shouldReceive('getRendered')
            ->once()
            ->withNoArgs()
            ->andReturn($rendered);

        $this->assertSame($rendered, $application->run($source));
    }
}
