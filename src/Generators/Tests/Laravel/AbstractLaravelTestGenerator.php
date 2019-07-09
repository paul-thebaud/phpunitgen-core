<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Tests\Laravel;

use PhpUnitGen\Core\Generators\Tests\BasicTestGenerator;
use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestImport;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Class AbstractLaravelTestGenerator.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
abstract class AbstractLaravelTestGenerator extends BasicTestGenerator
{
    /**
     * @var string The test category in Laravel app tests directory ("Unit" or "Feature").
     */
    protected $testCategory = 'Unit';

    /**
     * {@inheritdoc}
     */
    protected function getTestClassName(ReflectionClass $reflectionClass): string
    {
        $name = $reflectionClass->getName();

        $baseNamespace = $this->config->baseNamespace();
        if ($baseNamespace !== '') {
            $name = Str::replaceFirst($baseNamespace, '', $name);
        }

        return $this->config->baseTestNamespace().$this->testCategory.'\\'.$name.'Test';
    }

    /**
     * {@inheritdoc}
     */
    protected function addTestClassImports(TestClass $class): void
    {
        $class->addImport(new TestImport($this->config->baseTestNamespace().'TestCase'));
        $class->addImport(new TestImport($class->getReflectionClass()->getName()));
    }
}
