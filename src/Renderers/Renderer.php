<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Renderers;

use PhpUnitGen\Core\Contracts\Renderers\Renderer as RendererContract;
use PhpUnitGen\Core\Models\TestClass;
use PhpUnitGen\Core\Models\TestDocumentation;
use PhpUnitGen\Core\Models\TestImport;
use PhpUnitGen\Core\Models\TestMethod;
use PhpUnitGen\Core\Models\TestParameter;
use PhpUnitGen\Core\Models\TestProperty;
use PhpUnitGen\Core\Models\TestProvider;
use PhpUnitGen\Core\Models\TestStatement;
use PhpUnitGen\Core\Models\TestTrait;
use Tightenco\Collect\Support\Collection;

/**
 * Class Renderer.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
class Renderer implements RendererContract
{
    /**
     * @var int The current indentation for new lines.
     */
    protected $indentation;

    /**
     * @var RenderedLine[]|Collection The lines to render.
     */
    protected $lines;

    /**
     * Renderer constructor.
     */
    public function __construct()
    {
        $this->indentation = 0;
        $this->lines = new Collection();
    }

    /**
     * {@inheritdoc}
     */
    public function getRendered(): string
    {
        return $this->lines
            ->map(function (RenderedLine $line) {
                return $line->render();
            })
            ->implode("\n");
    }

    /**
     * {@inheritdoc}
     */
    public function visitTestImport(TestImport $import): void
    {
        $this->addLine('use '.$import->getName())
            ->when($import->getAlias(), function (string $alias) {
                $this->append(' as '.$alias);
            })
            ->append(';');
    }

    /**
     * {@inheritdoc}
     */
    public function visitTestClass(TestClass $class): void
    {
        $this->addLine('<?php')
            ->addLine()
            ->when($class->getNamespace(), function (string $namespace) {
                $this->addLine("namespace {$namespace};")
                    ->addLine();
            })
            ->whenNotEmpty($class->getImports(), function (Collection $imports) {
                $imports
                    ->sortBy(function (TestImport $import) {
                        return $import->getName();
                    })
                    ->each(function (TestImport $import) {
                        $import->accept($this);
                    });

                $this->addLine();
            })
            ->when($class->getDocumentation(), function (TestDocumentation $documentation) {
                $documentation->accept($this);
            })
            ->addLine("class {$class->getShortName()} extends TestCase")
            ->addLine('{')
            ->augmentIndent()
            ->whenNotEmpty($class->getTraits(), function (Collection $traits) {
                $traits
                    ->sortBy(function (TestTrait $trait) {
                        return $trait->getName();
                    })
                    ->each(function (TestTrait $trait) {
                        $trait->accept($this);
                    });

                $this->addLine();
            })
            ->whenNotEmpty($class->getProperties(), function (Collection $properties) {
                $properties->each(function (TestProperty $property) {
                    $property->accept($this);
                });
            })
            ->whenNotEmpty($class->getMethods(), function (Collection $methods) {
                $methods->each(function (TestMethod $method) {
                    $method->accept($this);
                });
            })
            ->when($this->lines->last()->getContent() === '', function () {
                $this->removeLine();
            })
            ->reduceIndent()
            ->addLine('}')
            ->addLine();
    }

    /**
     * {@inheritdoc}
     */
    public function visitTestTrait(TestTrait $trait): void
    {
        $this->addLine("use {$trait->getName()};");
    }

    /**
     * {@inheritdoc}
     */
    public function visitTestProperty(TestProperty $property): void
    {
        $this
            ->when($property->getDocumentation(), function (TestDocumentation $documentation) {
                $documentation->accept($this);
            })
            ->addLine("protected \${$property->getName()};")
            ->addLine();
    }

    /**
     * {@inheritdoc}
     */
    public function visitTestMethod(TestMethod $method): void
    {
        $this
            ->when($method->getDocumentation(), function (TestDocumentation $documentation) {
                $documentation->accept($this);
            })
            ->addLine("{$method->getVisibility()} function {$method->getName()}(")
            ->whenNotEmpty($method->getParameters(), function (Collection $parameters) {
                $lastKey = $parameters->keys()->last();

                $parameters->each(function (TestParameter $parameter, $key) use ($lastKey) {
                    $parameter->accept($this);

                    if ($key !== $lastKey) {
                        $this->append(', ');
                    }
                });
            })
            ->append('): void')
            ->addLine('{')
            ->augmentIndent()
            ->whenNotEmpty($method->getStatements(), function (Collection $statements) {
                $statements->each(function (TestStatement $statement) {
                    $statement->accept($this);
                });
            })
            ->reduceIndent()
            ->addLine('}')
            ->when($method->getProvider(), function (TestProvider $provider) {
                $provider->accept($this);
            })
            ->addLine();
    }

    /**
     * {@inheritdoc}
     */
    public function visitTestParameter(TestParameter $parameter): void
    {
        $this
            ->when($parameter->getType(), function (string $type) {
                $this->append($type.' ');
            })
            ->append('$'.$parameter->getName());
    }

    /**
     * {@inheritdoc}
     */
    public function visitTestProvider(TestProvider $provider): void
    {
        $this
            ->when($provider->getDocumentation(), function (TestDocumentation $documentation) {
                $documentation->accept($this);
            })
            ->addLine("public function {$provider->getName()}(): array")
            ->addLine('{')
            ->augmentIndent()
            ->addLine('return [')
            ->when($provider->getData(), function (array $data) {
                $this->augmentIndent();

                foreach ($data as $datum) {
                    $this->addLine('[')
                        ->append(implode(', ', $datum))
                        ->append('],');
                }

                $this->reduceIndent();
            })
            ->addLine('];')
            ->reduceIndent()
            ->addLine('}')
            ->addLine();
    }

    /**
     * {@inheritdoc}
     */
    public function visitTestStatement(TestStatement $statement): void
    {
        $this->whenNotEmpty($statement->getLines(), function (Collection $lines) {
            $this->addLine($lines->shift());

            $this->augmentIndent();

            $lines->each(function (string $line) {
                $this->addLine($line);
            });

            $this->reduceIndent();
        });
    }

    /**
     * {@inheritdoc}
     */
    public function visitTestDocumentation(TestDocumentation $documentation): void
    {
        $this->whenNotEmpty($documentation->getLines(), function (Collection $lines) {
            $this->addLine('/*');

            $lines->each(function (string $line) {
                $this->addLine(' *');

                if ($line !== '') {
                    $this->append(' '.$line);
                }
            });

            $this->addLine(' */');
        });
    }

    /**
     * Get the lines.
     *
     * @return Collection
     */
    public function getLines(): Collection
    {
        return $this->lines;
    }

    /**
     * Add a new line.
     *
     * @param string $content
     *
     * @return static
     */
    protected function addLine(string $content = ''): self
    {
        $this->lines->add(new RenderedLine($this->indentation, $content));

        return $this;
    }

    /**
     * Remove the last line.
     *
     * @return static
     */
    protected function removeLine(): self
    {
        $this->lines->pop();

        return $this;
    }

    /**
     * Prepends content to last line.
     *
     * @param string $content
     *
     * @return static
     */
    protected function prepend(string $content): self
    {
        $this->lines->last()->prepend($content);

        return $this;
    }

    /**
     * Appends content to last line.
     *
     * @param string $content
     *
     * @return static
     */
    protected function append(string $content): self
    {
        $this->lines->last()->append($content);

        return $this;
    }

    /**
     * Call the given callback if $value has a true boolean value.
     *
     * @param mixed    $value
     * @param callable $callback
     *
     * @return static
     */
    protected function when($value, callable $callback): self
    {
        if ($value) {
            $callback($value);
        }

        return $this;
    }

    /**
     * Call the given callback if $collection is not empty.
     *
     * @param Collection $collection
     * @param callable   $callback
     *
     * @return static
     */
    protected function whenNotEmpty(Collection $collection, callable $callback): self
    {
        if ($collection->isNotEmpty()) {
            $callback($collection);
        }

        return $this;
    }

    /**
     * Augment the indentation for new lines creation.
     *
     * @return static
     */
    protected function augmentIndent(): self
    {
        $this->indentation++;

        return $this;
    }

    /**
     * Reduce the indentation for new lines creation.
     *
     * @return static
     */
    protected function reduceIndent(): self
    {
        $this->indentation--;

        return $this;
    }
}
