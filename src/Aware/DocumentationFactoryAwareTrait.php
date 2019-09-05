<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Aware;

use PhpUnitGen\Core\Contracts\Aware\DocumentationFactoryAware;
use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory;

/**
 * Trait DocumentationFactoryAwareTrait.
 *
 * @see     DocumentationFactoryAware
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
trait DocumentationFactoryAwareTrait
{
    /**
     * @var DocumentationFactory
     */
    protected $documentationFactory;

    /**
     * {@inheritdoc}
     */
    public function setDocumentationFactory(DocumentationFactory $documentationFactory): void
    {
        $this->documentationFactory = $documentationFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentationFactory(): DocumentationFactory
    {
        return $this->documentationFactory;
    }
}
