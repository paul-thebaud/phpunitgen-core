<?php

declare(strict_types=1);

namespace Tests\PhpUnitGen\Core\Feature\Generators;

use PhpUnitGen\Core\CoreApplication;
use PhpUnitGen\Core\Parsers\Sources\LocalFileSource;
use Tests\PhpUnitGen\Core\TestCase;

/**
 * Class AbstractGeneratorTester.
 */
class AbstractGeneratorTester extends TestCase
{
    /**
     * Assert that the given file './tests/Features/Resources/Generators/<$expectedPath>.txt'
     * corresponds to the generation using './tests/Features/Resources/Generators/<$sourcePath>.txt'
     * and $config array.
     *
     * @param string $expectedPath
     * @param string $sourcePath
     * @param array  $config
     */
    protected function assertGeneratedIs(string $expectedPath, string $sourcePath, array $config = []): void
    {
        $application = CoreApplication::make($config);

        $actual = $application->run(
            new LocalFileSource($this->getFileAbsolutePath($sourcePath))
        );

        self::assertSame(
            file_get_contents($this->getFileAbsolutePath($expectedPath)),
            $actual->toString()
        );
    }

    /**
     * Get the file absolute path on "Generators" tests' resources path.
     *
     * @param string $file
     *
     * @return string
     */
    private function getFileAbsolutePath(string $file): string
    {
        $basePath = realpath(__DIR__.'/../Resources/Generators');

        return str_replace('/', DIRECTORY_SEPARATOR, "{$basePath}/{$file}.stub");
    }
}
