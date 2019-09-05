<?php

declare(strict_types=1);

namespace PhpUnitGen\Core\Contracts\Aware;

use PhpUnitGen\Core\Contracts\Generators\Factories\DocumentationFactory;

/**
 * Interface DocumentationFactoryAware.
 *
 * @author  Paul Thébaud <paul.thebaud29@gmail.com>
 * @author  Killian Hascoët <killianh@live.fr>
 * @license MIT
 */
interface DocumentationFactoryAware
{
    /**
     * @return DocumentationFactory
     */
    public function getDocumentationFactory(): DocumentationFactory;

    /**
     * @param DocumentationFactory $config
     */
    public function setDocumentationFactory(DocumentationFactory $config): void;
}
