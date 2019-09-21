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
            $this->makeName($reflectionClass)
        );

        $class->setDocumentation(
            $this->documentationFactory->makeForClass($class)
        );

        return $class;
    }

    /**
     * {@inheritdoc}
     */
    public function getTestBaseNamespace(): string
    {
        return trim($this->config->baseTestNamespace(), '\\');
    }

    /**
     * {@inheritdoc}
     */
    public function getTestSubNamespace(): string
    {
        return '';
    }

    /**
     * Get the test class name.
     *
     * @param ReflectionClass $reflectionClass
     *
     * @return string
     */
    public function makeName(ReflectionClass $reflectionClass): string
    {
        $name = $reflectionClass->getName();

        $baseNamespace = trim($this->config->baseNamespace(), '\\');
        if ($baseNamespace !== '') {
            $name = trim(Str::replaceFirst($baseNamespace, '', $name), '\\');
        }

        return trim($this->getTestBaseNamespace().$this->getTestSubNamespace(), '\\').'\\'.$name.'Test';
    }
}
