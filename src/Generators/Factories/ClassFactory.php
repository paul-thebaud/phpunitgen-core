<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Generators\Factories;

use PhpUnitGen\Core\Aware\ConfigAwareTrait;
use PhpUnitGen\Core\Aware\DocumentationFactoryAwareTrait;
use PhpUnitGen\Core\Contracts\Aware\ConfigAware;
use PhpUnitGen\Core\Contracts\Aware\DocumentationFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\ClassFactory as ClassFactoryContract;
use PhpUnitGen\Core\Helpers\Str;
use PhpUnitGen\Core\Models\TestClass;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Class ClassFactory.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class ClassFactory implements ClassFactoryContract, ConfigAware, DocumentationFactoryAware
{
    use ConfigAwareTrait;
    use DocumentationFactoryAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function make(ReflectionClass $reflectionClass): TestClass
    {
        $class = new TestClass(
            $reflectionClass,
            $this->makeNamespace($reflectionClass).'\\'.$this->makeShortName($reflectionClass)
        );

        $class->setDocumentation(
            $this->documentationFactory->makeForClass($class)
        );

        return $class;
    }

    /**
     * Get the test class namespace.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return string
     */
    protected function makeNamespace(ReflectionClass $reflectionClass): string
    {
        return trim($this->config->baseTestNamespace(), '\\');
    }

    /**
     * Get the test class short name.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return string
     */
    protected function makeShortName(ReflectionClass $reflectionClass): string
    {
        $name = $reflectionClass->getName();

        $baseNamespace = trim($this->config->baseNamespace(), '\\');
        if ($baseNamespace !== '') {
            $name = trim(Str::replaceFirst($baseNamespace, '', $name), '\\');
        }

        return $name.'Test';
    }
}
